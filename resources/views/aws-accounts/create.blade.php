@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Add AWS Credentials</h2>
        <form action="{{ route('aws-accounts.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block mb-1">Account Label</label>
                <input type="text" name="account_name" class="w-full border p-2 rounded" placeholder="Main Prod" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Access Key ID</label>
                <input type="text" name="access_key" class="w-full border p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Secret Access Key</label>
                <input type="password" name="secret_key" class="w-full border p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Default Region</label>
                <input type="text" name="default_region" class="w-full border p-2 rounded" value="us-east-1" required>
            </div>
            <button type="submit" class="w-full bg-green-600 text-white p-2 rounded font-bold">Save Account</button>
        </form>
    </div>
@endsection
