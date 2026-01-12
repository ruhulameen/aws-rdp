<?php

namespace App\Services;

use App\Models\AwsAccount;
use Aws\Ec2\Ec2Client;
use Illuminate\Support\Facades\Storage;
use Exception;

class AwsService
{
    protected ?Ec2Client $ec2 = null;
    protected ?AwsAccount $account = null;

    /**
     * Inject the AWS Account model to initialize the client.
     */
    public function setAccount(AwsAccount $account): self
    {
        $this->account = $account;
        $this->ec2 = new Ec2Client([
            'version'     => 'latest',
            'region'      => "us-east-1",
            'credentials' => [
                'key'    => $account->access_key,
                'secret' => $account->secret_key,
            ],
        ]);

        return $this;
    }

    /**
     * Complete Workflow: Creates SG, KeyPair, and Instance.
     * @throws Exception
     */
    public function createWindowsRdp(string $namePrefix): array
    {
        if (!$this->ec2) {
            throw new Exception("AWS Account not set.");
        }

        // Ensure we don't start with 'sg-'
        $cleanPrefix = str_starts_with($namePrefix, 'sg-')
            ? 'app-' . $namePrefix
            : $namePrefix;

        $identifier = $cleanPrefix . '-' . time();

        // 1. Create Security Group for RDP
        $groupId = $this->createRdpSecurityGroup("rdp-sg-{$identifier}");

        // 2. Create and Save Key Pair (Required for Windows Password)
        $keyName = "key-{$identifier}";
        $this->createKeyPair($keyName);

        // 3. Launch the Windows Instance
        $instanceId = $this->launchInstance($keyName, $groupId);

        return [
            'instance_id' => $instanceId,
            'key_name'    => $keyName,
            'group_id'    => $groupId,
            'region'      => 'us-east-1'
        ];
    }

    /**
     * Step 1: Create Security Group with Port 3389 open
     */
    protected function createRdpSecurityGroup(string $groupName): string
    {
        $group = $this->ec2->createSecurityGroup([
            'Description' => 'RDP access for Laravel App',
            'GroupName'   => $groupName,
        ]);

        $groupId = $group['GroupId'];

        // Allow RDP (3389) from all IPs (Or use request()->ip() for better security)
        $this->ec2->authorizeSecurityGroupIngress([
            'GroupId' => $groupId,
            'IpPermissions' => [
                [
                    'IpProtocol' => 'tcp',
                    'FromPort'   => 3389,
                    'ToPort'     => 3389,
                    'IpRanges'   => [['CidrIp' => '0.0.0.0/0']],
                ],
            ],
        ]);

        return $groupId;
    }

    /**
     * Step 2: Create Key Pair and store the .pem locally
     */
    protected function createKeyPair(string $keyName): void
    {
        $result = $this->ec2->createKeyPair(['KeyName' => $keyName]);

        // Store key material securely to decrypt password later
        Storage::disk('local')->put("aws-keys/{$keyName}.pem", $result['KeyMaterial']);
    }

    /**
     * Step 3: Launch Windows Instance
     */
    protected function launchInstance(string $keyName, string $groupId): string
    {
        // Note: ami-0c2b0d3fb0246199a is Windows Server 2022 in us-east-1.
        // You may want to store AMI IDs in your database per region.
        $result = $this->ec2->runInstances([
            'ImageId'        => '06777e7ef7441deff',
            'InstanceType'   => 'm7i-flex.large',
            'MinCount'       => 1,
            'MaxCount'       => 1,
            'KeyName'        => $keyName,
            'SecurityGroupIds' => [$groupId],
            'TagSpecifications' => [
                [
                    'ResourceType' => 'instance',
                    'Tags' => [['Key' => 'Name', 'Value' => $keyName]],
                ],
            ],
        ]);

        return $result['Instances'][0]['InstanceId'];
    }

    /**
     * Fetch Public IP of the instance
     */
    public function getInstancePublicIp(string $instanceId): ?string
    {
        $result = $this->ec2->describeInstances([
            'InstanceIds' => [$instanceId],
        ]);

        return $result['Reservations'][0]['Instances'][0]['PublicIpAddress'] ?? null;
    }

    /**
     * Step 4: Get and Decrypt Password
     * Note: Windows instances take 4-15 minutes to generate the password.
     */
    public function getWindowsPassword(string $instanceId, string $keyName): ?string
    {
        $result = $this->ec2->getPasswordData(['InstanceId' => $instanceId]);
        $encryptedPassword = $result['PasswordData'];

        if (empty($encryptedPassword)) {
            return null; // Not ready yet
        }

        $privateKey = Storage::disk('local')->get("aws-keys/{$keyName}.pem");

        $decryptedPassword = '';
        openssl_private_decrypt(
            base64_decode($encryptedPassword),
            $decryptedPassword,
            $privateKey
        );

        return $decryptedPassword;
    }

    /**
     * Step 5: Terminate Instance and Cleanup Resources
     * This method terminates the instance and deletes the associated SG and Key.
     */
    public function terminateInstance(string $instanceId, string $groupId, string $keyName): bool
    {
        if (!$this->ec2) {
            throw new Exception("AWS Account not set.");
        }

        try {
            // 1. Terminate the Instance
            $this->ec2->terminateInstances([
                'InstanceIds' => [$instanceId],
            ]);

            // 2. Delete the Key Pair from AWS
            $this->ec2->deleteKeyPair(['KeyName' => $keyName]);

            // 3. Delete the local PEM file
            if (Storage::disk('local')->exists("aws-keys/{$keyName}.pem")) {
                Storage::disk('local')->delete("aws-keys/{$keyName}.pem");
            }

            /**
             * 4. Delete the Security Group
             * NOTE: This will fail if the instance is still 'Shutting Down'.
             * In a production app, it is better to move SG deletion to a
             * delayed Queued Job (e.g., 2 minutes later).
             */
            try {
                // We wait a few seconds or attempt deletion.
                // If it fails with 'DependencyViolation', the instance is still active.
                $this->ec2->deleteSecurityGroup(['GroupId' => $groupId]);
            } catch (Exception $e) {
                // Log that SG cleanup needs to be handled later
                \Log::warning("Security Group {$groupId} could not be deleted immediately (DependencyViolation). Instance is likely still terminating.");
            }

            return true;
        } catch (Exception $e) {
            \Log::error("Termination failed for {$instanceId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get status for multiple instances at once.
     * @param array|string $instanceIds
     * @return array
     * @throws Exception
     */
    public function getMultipleInstancesStatus(array|string $instanceIds): array
    {
        if (!$this->ec2) {
            throw new Exception("AWS Account not set.");
        }

        // Ensure it's an array even if a single ID is passed
        $ids = is_array($instanceIds) ? $instanceIds : [$instanceIds];

        $result = $this->ec2->describeInstances([
            'InstanceIds' => $ids,
        ]);

        $statuses = [];

        // AWS returns results grouped by "Reservations"
        foreach ($result['Reservations'] as $reservation) {
            foreach ($reservation['Instances'] as $instance) {
                $statuses[$instance['InstanceId']] = [
                    'name'      => $instance['State']['Name'],
                    'public_ip' => $instance['PublicIpAddress'] ?? null,
                    'launch_time' => $instance['LaunchTime'],
                ];
            }
        }

        return $statuses;
    }
}
