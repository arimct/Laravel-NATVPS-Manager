<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('admin.nat-vps.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    NAT VPS: {{ $natVps->hostname }}
                </h2>
            </div>
            <a href="{{ route('admin.nat-vps.edit', $natVps) }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- VPS Details Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">VPS Details</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Hostname</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $natVps->hostname }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">VPS ID</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $natVps->vps_id }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Server</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $natVps->server ? $natVps->server->name : 'No Server' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Created</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $natVps->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- SSH Credentials Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">SSH Credentials</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Username</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 font-mono">
                                    {{ $natVps->ssh_username ?? 'Not set' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Password</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    @if($natVps->ssh_password)
                                        <span class="font-mono" x-data="{ show: false }">
                                            <span x-show="!show">••••••••</span>
                                            <span x-show="show" class="font-mono">{{ $natVps->ssh_password }}</span>
                                            <button type="button" @click="show = !show" class="ml-2 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                                <span x-show="!show">Show</span>
                                                <span x-show="show">Hide</span>
                                            </button>
                                        </span>
                                    @else
                                        Not set
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">SSH Port</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $natVps->ssh_port ?? 22 }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- User Assignment Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg md:col-span-2">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">User Assignment</h3>
                        
                        @if($natVps->user)
                            <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $natVps->user->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $natVps->user->email }}</p>
                                    </div>
                                </div>
                                <form action="{{ route('admin.nat-vps.unassign', $natVps) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this user assignment?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-600 rounded-md text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                                        </svg>
                                        Remove Assignment
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">This VPS is not assigned to any user. Only administrators can access it.</p>
                                <form action="{{ route('admin.nat-vps.assign', $natVps) }}" method="POST" class="flex items-end space-x-4">
                                    @csrf
                                    <div class="flex-1">
                                        <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select User</label>
                                        <select name="user_id" id="user_id" required
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Choose a user...</option>
                                            @foreach(\App\Models\User::where('role', \App\Enums\UserRole::User)->orderBy('name')->get() as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                        </svg>
                                        Assign User
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Domain Forwardings Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg md:col-span-2">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Domain Forwardings</h3>
                        
                        @if($natVps->domainForwardings->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No domain forwarding rules configured.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Domain</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Protocol</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Source Port</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Destination Port</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($natVps->domainForwardings as $forwarding)
                                            <tr>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $forwarding->domain }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $forwarding->protocol->value === 'https' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                        {{ strtoupper($forwarding->protocol->value) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $forwarding->source_port }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $forwarding->destination_port }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
