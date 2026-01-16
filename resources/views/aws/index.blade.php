@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded-xl overflow-hidden">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">My RDP Instances</h2>
            <span class="text-sm text-gray-500">Auto-refresh recommended while pending</span>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-bold">
                <th class="p-4">Instance ID / IP</th>
                <th class="p-4">Account</th>
                <th class="p-4">Status</th>
                <th class="p-4">Credentials</th>
                <th class="p-4">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($instances as $instance)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4">
                        <div class="font-bold text-gray-800">{{ $instance->instance_id }}</div>
                        <div class="text-sm text-blue-600">{{ $instance->public_ip ?? 'Allocating IP...' }}</div>
                    </td>
                    <td class="p-4 text-gray-600">
                        {{ $instance->awsAccount->account_name }}
                    </td>
                    <td class="p-4">
                        @if($instance->status == 'pending')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold animate-pulse">
                            <i class="fas fa-cog fa-spin mr-1"></i> Provisioning
                        </span>
                        @elseif($instance->status == 'ready')
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                            <i class="fas fa-check-circle mr-1"></i> Ready
                        </span>
                        @elseif($instance->status == 'running')
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                            <i class="fas fa-check-circle mr-1"></i> Running
                        </span>
                        @elseif($instance->status == 'terminated')
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                            <i class="fas fa-times mr-1"></i> Terminated
                        </span>
                        @else
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">Failed</span>
                        @endif
                    </td>
                    <td class="p-4">
                        @if($instance->password)
                            <div class="flex flex-col space-y-1">
                                <span class="text-xs text-gray-500">User: Administrator</span>
                                <div class="flex items-center space-x-2">
                                    <input type="password" value="{{ $instance->password }}" readonly
                                           class="bg-gray-100 border-none text-xs p-1 rounded w-32 focus:outline-none" id="pass-{{ $instance->id }}">
                                    <button onclick="togglePassword({{ $instance->id }})" class="text-gray-400 hover:text-blue-500">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Waiting for password...</span>
                        @endif
                    </td>
                    <td class="p-4">
                        <form action="{{ route('aws.destroy', $instance->id) }}" method="POST" onsubmit="return confirm('Terminate this instance?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 transition">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-10 text-center text-gray-400">
                        No instances found. Launch your first RDP!
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t">
            {{ $instances->links() }}
        </div>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById('pass-' + id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
@endsection
