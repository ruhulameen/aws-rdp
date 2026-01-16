<?php

namespace App\Jobs;

use App\Models\AwsAccount;
use App\Services\AwsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateWindowsRdpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // If AWS throttles the request, wait 30 seconds and try again
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        protected int $awsAccountId,
        protected string $region,
        protected string $namePrefix
    ) {}

    /**
     * @throws Exception
     */
    public function handle(AwsService $awsService): void
    {
        $account = AwsAccount::findOrFail($this->awsAccountId);

        // 1. Point the service to the right account/region
        $awsService->setAccount($account, $this->region);

        try {
            // 2. This method creates SG, KeyPair, Instance, and saves to DB
            $result = $awsService->createWindowsRdp($this->namePrefix, $this->region);

            $instance = $result['instance'];

            // 3. Chain the Password/IP Polling Job
            // We delay by 4 minutes because Windows password data isn't ready immediately
            CheckRdpPasswordJob::dispatch($instance)->delay(now()->addMinutes(4));

            Log::info("Job Success: RDP created in {$this->region} for Account ID: {$this->awsAccountId}");

        } catch (Exception $e) {
            Log::error("Job Failed: " . $e->getMessage());
            throw $e; // Re-throw so Laravel handles retries
        }
    }
}
