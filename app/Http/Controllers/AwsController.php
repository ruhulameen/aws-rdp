<?php
namespace App\Http\Controllers;

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

    /**
     * Display a listing of all RDP instances.
     */
    public function index()
    {
        $instances = RdpInstance::with('awsAccount')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('aws.index', compact('instances'));
    }

    /**
     * Show the form for creating a new RDP.
     */
    public function create()
    {
        $accounts = AwsAccount::all();
        return view('aws.create', compact('accounts'));
    }

    /**
     * Store a newly created RDP in AWS and Database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'aws_account_id' => 'required|exists:aws_accounts,id',
            'name_prefix'    => 'nullable|string|max:20',
        ]);

        try {
            // 1. Fetch Account and Set Service
            $account = AwsAccount::findOrFail($request->aws_account_id);
            $this->awsService->setAccount($account);

            // 2. Provision on AWS
            $namePrefix = $request->name_prefix ?? 'rdp-instance';
            $awsData = $this->awsService->createWindowsRdp($namePrefix);

            // 3. Create Local Record
            $instance = RdpInstance::create([
                'aws_account_id' => $account->id,
                'instance_id'    => $awsData['instance_id'],
                'region'         => $awsData['region'],
                'key_name'       => $awsData['key_name'],
                'group_id'       => $awsData['group_id'],
                'status'         => 'pending',
            ]);

            // 4. Dispatch the Background Job to poll for Password & IP
            // We delay it by 4 minutes because Windows takes time to initialize
            CheckRdpPasswordJob::dispatch($instance)->delay(now()->addMinutes(4));

            return redirect()->route('aws.index')
                ->with('success', 'RDP Provisioning started. Password will be available in ~5 minutes.');

        } catch (\Exception $e) {
            Log::error("RDP Creation Failed: " . $e->getMessage());
            return back()->withErrors('Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the details of a specific RDP.
     */
    public function show(string $id)
    {
        $instance = RdpInstance::with('awsAccount')->findOrFail($id);
        return view('aws.show', compact('instance'));
    }

    /**
     * Terminate and Remove the RDP instance.
     */
    public function destroy(string $id)
    {
        $instance = RdpInstance::with('awsAccount')->findOrFail($id);

        try {
            // 1. Set the correct account credentials
            $this->awsService->setAccount($instance->awsAccount);

            // 2. Perform termination and cleanup
            $this->awsService->terminateInstance(
                $instance->instance_id,
                $instance->group_id, // Ensure you saved group_id in your table
                $instance->key_name
            );

            // 3. Remove from local database
            $instance->delete();

            return redirect()->route('aws.index')->with('success', 'RDP terminated and cleaned up successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Termination Error: ' . $e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function syncAll()
    {
        $instances = RdpInstance::all();

        // Group by AWS Account (because each account needs its own Client)
        $groupedByAccount = $instances->groupBy('aws_account_id');

        foreach ($groupedByAccount as $accountId => $accountInstances) {
            $account = AwsAccount::find($accountId);
            $this->awsService->setAccount($account);

            // Extract just the instance IDs
            $awsIds = $accountInstances->pluck('instance_id')->toArray();

            // Fetch all statuses in one call
            $awsStatuses = $this->awsService->getMultipleInstancesStatus($awsIds);

            // Update your database
            foreach ($accountInstances as $localInstance) {
                if (isset($awsStatuses[$localInstance->instance_id])) {
                    $statusData = $awsStatuses[$localInstance->instance_id];
                    $localInstance->update([
                        'status'    => $statusData['name'],
                        'public_ip' => $statusData['public_ip']
                    ]);
                }
            }
        }

        return back()->with('success', 'All instance statuses synced with AWS.');
    }
}
