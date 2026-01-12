<?php

namespace App\Jobs;

use App\Models\RdpInstance;
use App\Services\AwsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckRdpPasswordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * At 1-minute intervals, 15 tries = 15 minutes of polling.
     */
    public $tries = 15;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected RdpInstance $rdpInstance
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AwsService $awsService): void
    {
        // 1. Initialize the AWS Service with the specific account credentials
        $awsService->setAccount($this->rdpInstance->awsAccount);

        try {
            // 2. Update Public IP if not already set
            if (!$this->rdpInstance->public_ip) {
                $publicIp = $awsService->getInstancePublicIp($this->rdpInstance->instance_id);
                if ($publicIp) {
                    $this->rdpInstance->update(['public_ip' => $publicIp]);
                }
            }

            // 3. Attempt to get the Windows Password
            $password = $awsService->getWindowsPassword(
                $this->rdpInstance->instance_id,
                $this->rdpInstance->key_name
            );

            if ($password) {
                // Password found! Update DB and finish.
                $this->rdpInstance->update([
                    'password' => $password,
                    'status' => 'ready'
                ]);

                Log::info("RDP Password retrieved for Instance: {$this->rdpInstance->instance_id}");
                return;
            }

            // 4. If password is not ready yet, re-queue the job with a 60-second delay
            Log::info("Password not ready for {$this->rdpInstance->instance_id}. Retrying in 60s...");
            $this->release(60);

        } catch (\Exception $e) {
            Log::error("Error in CheckRdpPasswordJob: " . $e->getMessage());

            // If we've exhausted all tries, mark as failed
            if ($this->attempts() >= $this->tries) {
                $this->rdpInstance->update(['status' => 'failed']);
            }

            throw $e;
        }
    }
}
