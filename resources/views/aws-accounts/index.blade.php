@extends('layouts.app')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">AWS Accounts</h2>
            <a href="{{ route('aws-accounts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Add Account</a>
        </div>

        <table class="w-full border-collapse">
            <thead>
            <tr class="bg-gray-100 text-left text-sm">
                <th class="p-3">Name</th>
                <th class="p-3">Region</th>
                <th class="p-3">Access Key (Last 4)</th>
                <th class="p-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($accounts as $account)
                <tr class="border-b">
                    <td class="p-3">{{ $account->account_name }}</td>
                    <td class="p-3">{{ $account->default_region }}</td>
                    <td class="p-3 text-gray-500">****{{ substr($account->access_key, -4) }}</td>
                    <td class="p-3 flex gap-2">
                        <a href="{{ route('aws-accounts.edit', $account->id) }}" class="text-blue-500">Edit</a>
                        <form action="{{ route('aws-accounts.destroy', $account->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-500" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
