<?php

namespace App\Http\Controllers;

use App\Models\AwsAccount;
use Illuminate\Http\Request;

class AwsAccountController extends Controller
{
    public function index()
    {
        $accounts = AwsAccount::latest()->paginate(10);
        return view('aws-accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('aws-accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name'   => 'required|string|max:255',
            'access_key'     => 'required|string',
            'secret_key'     => 'required|string',
            'default_region' => 'required|string|max:50',
        ]);

        AwsAccount::create($validated);

        return redirect()->route('aws-accounts.index')
            ->with('success', 'AWS Account added successfully.');
    }

    public function edit(AwsAccount $awsAccount)
    {
        return view('aws-accounts.edit', compact('awsAccount'));
    }

    public function update(Request $request, AwsAccount $awsAccount)
    {
        $validated = $request->validate([
            'account_name'   => 'required|string|max:255',
            'access_key'     => 'required|string',
            'secret_key'     => 'required|string',
            'default_region' => 'required|string|max:50',
        ]);

        $awsAccount->update($validated);

        return redirect()->route('aws-accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(AwsAccount $awsAccount)
    {
        $awsAccount->delete();
        return redirect()->route('aws-accounts.index')
            ->with('success', 'Account deleted.');
    }
}
