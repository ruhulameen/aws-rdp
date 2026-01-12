<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RdpInstance;
use App\Models\AwsAccount;
use App\Services\AwsService;

class SyncRdpStatus extends Command
{
    protected $signature = 'rdp:sync-status';
    protected $description = 'Sync all RDP instance statuses with AWS';

    public function handle(AwsService $awsService): void
    {

        $instances = RdpInstance::withTrashed()->get();
        $groupedByAccount = $instances->groupBy('aws_account_id');

        foreach ($groupedByAccount as $accountId => $accountInstances) {
            $account = AwsAccount::find($accountId);
            if (!$account) continue;

            $this->info("Syncing account: {$account->account_name}");
            $awsService->setAccount($account);

            $awsIds = $accountInstances->pluck('instance_id')->toArray();

            try {
                // Bulk fetch status from AWS
                $awsStatuses = $awsService->getMultipleInstancesStatus($awsIds);

                foreach ($accountInstances as $localInstance) {
                    if (isset($awsStatuses[$localInstance->instance_id])) {
                        $data = $awsStatuses[$localInstance->instance_id];
                        $localInstance->update([
                            'status' => $data['name'],
                            'public_ip' => $data['public_ip']
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->error("Failed to sync account {$account->id}: " . $e->getMessage());
            }
        }

        $this->info('Sync completed!');
    }
}
