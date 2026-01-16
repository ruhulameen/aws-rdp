<?php

namespace App\Http\Controllers;

use App\Jobs\CreateWindowsRdpJob;
use App\Models\AwsAccount;
use App\Models\RdpInstance;
use App\Services\AwsService;
use App\Jobs\CheckRdpPasswordJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AwsController extends Controller
{
    protected AwsService $awsService;

    public function __construct(AwsService $awsService)
    {
        $this->awsService = $awsService;
    }

    public function index()
    {
        $instances = RdpInstance::with('awsAccount')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('aws.index', compact('instances'));
    }

    public function create()
    {
        $accounts = AwsAccount::all();

        $regions = [
            'us-east-1' => 'US East (N. Virginia)',
            'us-east-2' => 'US East (Ohio)',
            'us-west-1' => 'US West (N. California)',
            'us-west-2' => 'US West (Oregon)',
        ];

        // FIX: Pass 'regions' as a string, not the variable $regions
        return view('aws.create', compact('accounts', 'regions'));
    }

    /**
     * Store a newly created RDP with Region Selection.
     */
    public function store(Request $request)
    {
        $request->validate([
            'aws_account_id' => 'required|exists:aws_accounts,id',
            'region'         => 'required|string|in:us-east-1,us-east-2,us-west-1,us-west-2',
            'name_prefix'    => 'nullable|string|max:20',
        ]);

        try {
            $account = AwsAccount::findOrFail($request->aws_account_id);
            $namePrefix = $request->name_prefix ?? 'rdp';

            // 1. Initialize service with account
            $this->awsService->setAccount($account);

            // 2. Call the service (Service now handles limit check and DB creation internally)
            $result = $this->awsService->createWindowsRdp($namePrefix, $request->region);
            $instance = $result['instance'];

            // 3. Dispatch polling job
            CheckRdpPasswordJob::dispatch($instance)->delay(now()->addMinutes(4));

            return redirect()->route('aws.index')
                ->with('success', "RDP creation started in {$request->region}. Password ready in ~5m.");

        } catch (\Exception $e) {
            Log::error("RDP Creation Failed: " . $e->getMessage());
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    /**
     * Terminate the RDP instance using its stored Region.
     */
    public function destroy(string $id) // Use Route Model Binding
    {
        $instance = RdpInstance::with('awsAccount')->findOrFail($id);
        try {
            // Service handles switching to the correct region based on $instance->region
            $success = $this->awsService->terminateInstance($instance);

            if ($success) {
                return redirect()->route('aws.index')->with('success', 'RDP termination initiated.');
            }

            return back()->withErrors('Termination failed. Check logs.');
        } catch (\Exception $e) {
            Log::error("Manual Termination Error: " . $e->getMessage());
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * Sync Statuses across multiple regions.
     */
    public function syncAll()
    {
        $accounts = AwsAccount::all();
        $regions = ['us-east-1', 'us-east-2', 'us-west-1', 'us-west-2'];

        try {
            foreach ($accounts as $account) {
                foreach ($regions as $region) {
                    $this->awsService->setAccount($account)->syncInstanceStatuses($region);
                }
            }
            return back()->with('success', 'All instances synced across all regions.');
        } catch (\Exception $e) {
            return back()->withErrors('Sync error: ' . $e->getMessage());
        }
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:100',
            'prefix' => 'nullable|string|max:10'
        ]);

        $requested = $request->count;
        $prefix = $request->prefix ?? 'rdp';

        $inventory = $this->awsService->getGlobalInventory();

        $totalAvailable = array_sum(array_map('array_sum', $inventory));

        if ($requested > $totalAvailable) {
            return back()->withErrors("Not enough capacity. Total slots available: $totalAvailable");
        }

        $dispatched = 0;
        foreach ($inventory as $accountId => $regions) {
            foreach ($regions as $region => $freeSlots) {
                for ($i = 0; $i < $freeSlots; $i++) {
                    if ($dispatched >= $requested) break 2;

                    // 2. Dispatch the creation to the background
                    CreateWindowsRdpJob::dispatch(
                        $accountId,
                        $region,
                        "{$prefix}-" . ($dispatched + 1)
                    );

                    $dispatched++;
                }
            }
        }

        return redirect()->route('aws.index')->with('success', "Dispatched $dispatched RDP creation jobs.");
    }
}
