@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Provision Windows RDP</h2>

        <form action="{{ route('aws.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Select AWS Account</label>
                <select name="aws_account_id" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_name }} ({{ $account->default_region }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">RDP Label (Optional)</label>
                <input type="text" name="name_prefix" placeholder="e.g. Workstation-Alpha"
                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="bg-blue-50 p-4 rounded-lg mb-6 flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                <p class="text-sm text-blue-700">
                    Windows instances take approximately 4-7 minutes to initialize.
                    Your password will automatically appear in the dashboard once ready.
                </p>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg">
                Launch Instance
            </button>
        </form>
    </div>
@endsection
