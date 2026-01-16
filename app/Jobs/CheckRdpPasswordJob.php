<?php

namespace App\Jobs;

use App\Models\RdpInstance;
use App\Services\AwsService;
use Exception;
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
     * Polling settings: 20 tries at 60s intervals = 20 minutes max.
     */
    public int $tries = 20;
    public int $backoff = 60;

    public function __construct(
        protected RdpInstance $rdpInstance
    ) {}

    /**
     * @throws Exception
     */
    public function handle(AwsService $awsService): void
    {
        // 1. CRITICAL: Re-initialize the service with the stored Region
        $awsService->setAccount(
            $this->rdpInstance->awsAccount,
            $this->rdpInstance->region
        );

        try {
            // 2. Sync Public IP (Windows doesn't show IP immediately on launch)
            if (!$this->rdpInstance->public_ip) {
                $publicIp = $awsService->getInstancePublicIp($this->rdpInstance->instance_id);
                if ($publicIp) {
                    $this->rdpInstance->update(['public_ip' => $publicIp]);
                }
            }

            // 3. Attempt to fetch and decrypt the password
            // We pass the whole model to the service so it stays region-aware
            $password = $awsService->getWindowsPassword($this->rdpInstance);

            if ($password) {
                $this->rdpInstance->update([
                    'password' => $password,
                    'status'   => 'running'
                ]);

                Log::info("RDP Ready: {$this->rdpInstance->instance_id} in {$this->rdpInstance->region}");
                return;
            }

            // 4. Not ready? Release back to queue for a retry
            $this->release(60);

        } catch (Exception $e) {
            Log::error("Job Failed for {$this->rdpInstance->instance_id}: " . $e->getMessage());

            if ($this->attempts() >= $this->tries) {
                $this->rdpInstance->update(['status' => 'failed']);
            }

            throw $e;
        }
    }
}
