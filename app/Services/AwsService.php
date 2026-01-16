<?php

namespace App\Services;

use App\Models\AwsAccount;
use App\Models\RdpInstance;
use Aws\Ec2\Ec2Client;
use Illuminate\Support\Facades\Storage;
use Exception;
use Log;

class AwsService
{
    protected ?Ec2Client $ec2 = null;
    protected ?AwsAccount $account = null;

    /**
     * Regional Windows Server 2022 AMI Map
     * @throws Exception
     */
    public function getAmiForRegion(string $region): string
    {
        $amis = [
            'us-east-1' => 'ami-06777e7ef7441deff',
            'us-east-2' => 'ami-013e43c5ba6d06126',
            'us-west-1' => 'ami-0bce52ac08a12d564',
            'us-west-2' => 'ami-02deb4df6847d746c',
            // Add more regions as needed
        ];
        return $amis[$region] ?? throw new Exception("Unsupported region: {$region}");
    }

    /**
     * Initialize client for a specific account and region.
     */
    public function setAccount(AwsAccount $account, string $region = 'us-east-1'): self
    {
        $this->account = $account;
        $this->ec2 = new Ec2Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $account->access_key,
                'secret' => $account->secret_key,
            ],
        ]);
        return $this;
    }

    /**
     * Complete Workflow with Region Limit Check
     * @throws Exception
     */
    public function createWindowsRdp(string $namePrefix, string $region): array
    {
        if (!$this->account) throw new Exception("AWS Account not set.");

        // 1. Enforce 4 instances per region limit
        $count = RdpInstance::where('aws_account_id', $this->account->id)
            ->where('region', $region)
            ->where('status', '!=', 'terminated')
            ->count();

        if ($count >= 4) {
            throw new Exception("Limit reached: Only 4 active RDPs allowed in {$region}.");
        }

        // 2. Switch client to the requested region
        $this->setAccount($this->account, $region);

        $identifier = $namePrefix . '-' . time();

        try {
            // Step A: Create SG
            $groupId = $this->createRdpSecurityGroup("rdp-sg-{$identifier}");

            // Step B: Create Key
            $keyName = "key-{$identifier}";
            $this->createKeyPair($keyName);

            // Step C: Launch
            $instanceId = $this->launchInstance($keyName, $groupId, $region);

            // Step D: Record in DB
            $rdp = RdpInstance::create([
                'aws_account_id' => $this->account->id,
                'instance_id'    => $instanceId,
                'region'         => $region,
                'key_name'       => $keyName,
                'group_id'       => $groupId,
                'status'         => 'pending'
            ]);

            $rdp->load('awsAccount');

            return ['success' => true, 'instance' => $rdp];

        } catch (Exception $e) {
            Log::error("RDP Creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Password (Handles dynamic region switching)
     */
    public function getWindowsPassword(RdpInstance $rdp): ?string
    {
        // Re-init client to the instance's specific region
        $this->setAccount($rdp->awsAccount, $rdp->region);

        $result = $this->ec2->getPasswordData(['InstanceId' => $rdp->instance_id]);
        $encryptedPassword = $result['PasswordData'] ?? null;

        if (!$encryptedPassword) return null;

        $privateKey = Storage::disk('local')->get("aws-keys/{$rdp->key_name}.pem");
        if (!$privateKey) throw new Exception("Private key not found locally.");

        $decryptedPassword = '';
        openssl_private_decrypt(base64_decode($encryptedPassword), $decryptedPassword, $privateKey);

        return $decryptedPassword;
    }

    /**
     * Terminate and Cleanup (Handles dynamic region switching)
     */
    public function terminateInstance(RdpInstance $rdp): bool
    {
        $this->setAccount($rdp->awsAccount, $rdp->region);

        try {
            // 1. Kill Instance
            $this->ec2->terminateInstances(['InstanceIds' => [$rdp->instance_id]]);

            // 2. Cleanup Key from AWS & Local
            $this->ec2->deleteKeyPair(['KeyName' => $rdp->key_name]);
            Storage::disk('local')->delete("aws-keys/{$rdp->key_name}.pem");

            // 3. Mark as Terminated in DB
            $rdp->update(['status' => 'terminated']);

            // 4. Try SG Deletion (might fail if instance isn't fully stopped yet)
            try {
                $this->ec2->deleteSecurityGroup(['GroupId' => $rdp->group_id]);
            } catch (Exception $sgE) {
                Log::warning("SG {$rdp->group_id} cleanup pending termination: " . $sgE->getMessage());
            }

            return true;
        } catch (Exception $e) {
            Log::error("Termination error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync Status for all active instances
     */
    public function syncInstanceStatuses(string $region): void
    {
        if (!$this->account) return;
        $this->setAccount($this->account, $region);

        $activeInstances = RdpInstance::where('aws_account_id', $this->account->id)
            ->where('region', $region)
            ->where('status', '!=', 'terminated')
            ->pluck('instance_id')
            ->toArray();

        if (empty($activeInstances)) return;

        $result = $this->ec2->describeInstances(['InstanceIds' => $activeInstances]);

        foreach ($result['Reservations'] as $res) {
            foreach ($res['Instances'] as $ins) {
                RdpInstance::where('instance_id', $ins['InstanceId'])
                    ->update(['status' => $ins['State']['Name']]);
            }
        }
    }

    // --- Helper Methods (Protected) ---

    protected function createRdpSecurityGroup(string $groupName): string
    {
        $group = $this->ec2->createSecurityGroup([
            'Description' => 'RDP Access',
            'GroupName'   => $groupName,
        ]);
        $groupId = $group['GroupId'];

        $this->ec2->authorizeSecurityGroupIngress([
            'GroupId' => $groupId,
            'IpPermissions' => [[
                'IpProtocol' => 'tcp',
                'FromPort' => 3389,
                'ToPort' => 3389,
                'IpRanges' => [['CidrIp' => '0.0.0.0/0']],
            ]],
        ]);

        return $groupId;
    }

    protected function createKeyPair(string $keyName): void
    {
        $result = $this->ec2->createKeyPair(['KeyName' => $keyName]);
        Storage::disk('local')->put("aws-keys/{$keyName}.pem", $result['KeyMaterial']);
    }

    /**
     * @throws Exception
     */
    protected function launchInstance(string $keyName, string $groupId, string $region): string
    {
        $result = $this->ec2->runInstances([
            'ImageId'        => $this->getAmiForRegion($region),
            'InstanceType'   => 'm7i-flex.large',
            'MinCount'       => 1,
            'MaxCount'       => 1,
            'KeyName'        => $keyName,
            'SecurityGroupIds' => [$groupId],
            'TagSpecifications' => [[
                'ResourceType' => 'instance',
                'Tags' => [['Key' => 'Name', 'Value' => $keyName]],
            ]],
        ]);
        return $result['Instances'][0]['InstanceId'];
    }

    public function getInstancePublicIp(string $instanceId): ?string
    {
        $result = $this->ec2->describeInstances([
            'InstanceIds' => [$instanceId],
        ]);

        return $result['Reservations'][0]['Instances'][0]['PublicIpAddress'] ?? null;
    }

    /**
     * Calculate total available slots across ALL accounts and ALL regions.
     * Returns a nested array: [account_id => [region => free_slots]]
     */
    public function getGlobalInventory(): array
    {
        $accounts = AwsAccount::all();
        $regions = ['us-east-1', 'us-east-2', 'us-west-1', 'us-west-2'];
        $inventory = [];

        foreach ($accounts as $account) {
            foreach ($regions as $region) {
                $used = RdpInstance::where('aws_account_id', $account->id)
                    ->where('region', $region)
                    ->where('status', '!=', 'terminated')
                    ->count();

                $free = 4 - $used; // 4 is your per-region limit
                if ($free > 0) {
                    $inventory[$account->id][$region] = $free;
                }
            }
        }

        return $inventory;
    }
}
