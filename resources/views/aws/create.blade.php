@extends('layouts.app')
<script src="//unpkg.com/alpinejs" defer></script>

@section('content')
    <div class="max-w-3xl mx-auto" x-data="{ mode: 'single' }">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <div class="bg-gray-50 border-b p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Provision Windows RDP</h2>

                <div class="inline-flex p-1 bg-gray-200 rounded-lg">
                    <button @click="mode = 'single'"
                            :class="mode === 'single' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                            class="px-4 py-1.5 rounded-md text-sm font-bold transition">Single</button>
                    <button @click="mode = 'bulk'"
                            :class="mode === 'bulk' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                            class="px-4 py-1.5 rounded-md text-sm font-bold transition">Bulk (Up to 100)</button>
                </div>
            </div>

            <div class="p-8">
                <form x-show="mode === 'single'" action="{{ route('aws.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">AWS Account</label>
                            <select name="aws_account_id" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Region</label>
                            <select name="region" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                                @foreach($regions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2">Instance Name Prefix</label>
                        <input type="text" name="name_prefix" placeholder="e.g. Workstation-A"
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg">
                        Launch Single Instance
                    </button>
                </form>

                <form x-show="mode === 'bulk'" action="{{ route('aws.bulk-store') }}" method="POST">
                    @csrf
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
                        <p class="text-sm text-amber-800">
                            <strong>Bulk Mode:</strong> The system will automatically distribute instances across all available accounts and regions to maximize your capacity.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Total RDPs to Create</label>
                            <input type="number" name="count" min="1" max="100" value="5"
                                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                            <p class="text-xs text-gray-500 mt-1">Recommended: Max 100 per batch.</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Global Prefix</label>
                            <input type="text" name="prefix" placeholder="e.g. Bulk-Node"
                                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg">
                        Dispatch Bulk Provisioning Jobs
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100 flex items-center text-gray-500 text-sm">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>AWS provisioning and Windows boot-up usually takes 5-10 minutes.</span>
                </div>
            </div>
        </div>
    </div>
@endsection
