<?php

namespace App\Http\Controllers;

use App\Models\AwsAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AwsAccountController extends Controller
{
    public function index(Request $request)
    {
        $pageParam = $request->input('page', 1);
        $accounts = AwsAccount::latest()->paginate(10, ['*'], 'page', $pageParam);
        $isAdmin = $request->routeIs('admin.*');
        $routePrefix = $isAdmin ? '/admin/aws-accounts' : '/user/aws-accounts';
        $page = $isAdmin ? 'Admin/AwsAccounts/Index' : 'User/AwsAccounts/Index';

        return Inertia::render($page, [
            'accounts' => $accounts->items(),
            'pagination' => [
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
                'route' => $routePrefix,
            ],
            'success' => session('success'),
            'error' => session('error'),
            'warning' => session('warning'),
        ]);
    }

    public function create()
    {
        $isAdmin = request()->routeIs('admin.*');
        $page = $isAdmin ? 'Admin/AwsAccounts/Create' : 'User/AwsAccounts/Create';

        return Inertia::render($page);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'default_region' => 'required|string|max:50',
        ]);
        AwsAccount::create($validated);

        return redirect()->back()->with('success', 'AWS Account added successfully.');
    }

    public function edit(AwsAccount $awsAccount)
    {
        $isAdmin = request()->routeIs('admin.*');
        $page = $isAdmin ? 'Admin/AwsAccounts/Edit' : 'User/AwsAccounts/Edit';

        return Inertia::render($page, [
            'account' => $awsAccount, // Pass as 'account' prop for Vue compatibility
        ]);
    }

    public function update(Request $request, AwsAccount $awsAccount)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'default_region' => 'required|string|max:50',
        ]);
        $awsAccount->update($validated);

        return redirect()->back()->with('success', 'Account updated successfully.');
    }

    public function destroy(AwsAccount $awsAccount)
    {
        $awsAccount->delete();

        return redirect()->back()->with('success', 'Account deleted.');
    }
}
