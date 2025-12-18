<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('user.vps.show', $natVps) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Domain Forwarding: {{ $natVps->hostname }}
                </h2>
            </div>
            @if($apiOffline)
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    API Offline
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-300" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($apiOffline)
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-300" role="alert">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>API is currently unavailable. Showing cached data. Some actions may not work.</span>
                    </div>
                </div>
            @endif

            <!-- Create New Forwarding Rule Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add New Forwarding Rule</h3>
                    
                    <form action="{{ route('user.vps.domain-forwarding.store', $natVps) }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <!-- Domain -->
                            <div class="lg:col-span-2">
                                <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Domain</label>
                                <input type="text" 
                                       name="domain" 
                                       id="domain" 
                                       value="{{ old('domain') }}"
                                       placeholder="example.com"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                       required>
                                @error('domain')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Protocol -->
                            <div>
                                <label for="protocol" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Protocol</label>
                                <select name="protocol" 
                                        id="protocol" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        required>
                                    @foreach($protocols as $protocol)
                                        <option value="{{ $protocol->value }}" {{ old('protocol') === $protocol->value ? 'selected' : '' }}>
                                            {{ strtoupper($protocol->value) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('protocol')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Source Port -->
                            <div>
                                <label for="source_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Source Port</label>
                                <input type="number" 
                                       name="source_port" 
                                       id="source_port" 
                                       value="{{ old('source_port', 80) }}"
                                       min="1"
                                       max="65535"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                       required>
                                @error('source_port')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Destination Port -->
                            <div>
                                <label for="destination_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dest Port</label>
                                <input type="number" 
                                       name="destination_port" 
                                       id="destination_port" 
                                       value="{{ old('destination_port', 80) }}"
                                       min="1"
                                       max="65535"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                       required>
                                @error('destination_port')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" 
                                    @if($apiOffline) disabled @endif
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150
                                           {{ $apiOffline ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Forwarding Rules -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Forwarding Rules</h3>
                    
                    @if($domainForwardings->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">No domain forwarding rules configured.</p>
                    @else
                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Domain</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Protocol</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Source Port</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dest Port</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($domainForwardings as $forwarding)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $forwarding->domain }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $forwarding->protocol->value === 'https' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                    {{ strtoupper($forwarding->protocol->value) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $forwarding->source_port }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $forwarding->destination_port }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium" x-data="{ confirmDelete: false }">
                                                <button type="button" 
                                                        @click="confirmDelete = true"
                                                        @if($apiOffline) disabled @endif
                                                        class="{{ $apiOffline ? 'text-gray-400 cursor-not-allowed' : 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300' }}">
                                                    Delete
                                                </button>

                                                <!-- Delete Confirmation Modal -->
                                                <div x-show="confirmDelete" 
                                                     x-cloak
                                                     class="fixed inset-0 z-50 overflow-y-auto" 
                                                     aria-labelledby="modal-title" 
                                                     role="dialog" 
                                                     aria-modal="true">
                                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                        <div x-show="confirmDelete" 
                                                             x-transition:enter="ease-out duration-300"
                                                             x-transition:enter-start="opacity-0"
                                                             x-transition:enter-end="opacity-100"
                                                             x-transition:leave="ease-in duration-200"
                                                             x-transition:leave-start="opacity-100"
                                                             x-transition:leave-end="opacity-0"
                                                             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                                                             @click="confirmDelete = false"></div>

                                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                                        <div x-show="confirmDelete"
                                                             x-transition:enter="ease-out duration-300"
                                                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                             x-transition:leave="ease-in duration-200"
                                                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                             class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                                <div class="sm:flex sm:items-start">
                                                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                                                                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                                                            Delete Forwarding Rule
                                                                        </h3>
                                                                        <div class="mt-2">
                                                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                                Are you sure you want to delete the forwarding rule for <span class="font-semibold">{{ $forwarding->domain }}</span>? This action cannot be undone.
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                                <form action="{{ route('user.vps.domain-forwarding.destroy', [$natVps, $forwarding]) }}" method="POST" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" 
                                                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                                <button type="button" 
                                                                        @click="confirmDelete = false"
                                                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden space-y-4">
                            @foreach($domainForwardings as $forwarding)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4" x-data="{ confirmDelete: false }">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $forwarding->domain }}</p>
                                            <span class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $forwarding->protocol->value === 'https' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                {{ strtoupper($forwarding->protocol->value) }}
                                            </span>
                                        </div>
                                        <button type="button" 
                                                @click="confirmDelete = true"
                                                @if($apiOffline) disabled @endif
                                                class="{{ $apiOffline ? 'text-gray-400 cursor-not-allowed' : 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300' }} text-sm">
                                            Delete
                                        </button>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <span>Port {{ $forwarding->source_port }} → {{ $forwarding->destination_port }}</span>
                                    </div>

                                    <!-- Mobile Delete Confirmation Modal -->
                                    <div x-show="confirmDelete" 
                                         x-cloak
                                         class="fixed inset-0 z-50 overflow-y-auto" 
                                         aria-labelledby="modal-title" 
                                         role="dialog" 
                                         aria-modal="true">
                                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div x-show="confirmDelete" 
                                                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                                                 @click="confirmDelete = false"></div>

                                            <div x-show="confirmDelete"
                                                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                    <div class="text-center">
                                                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 mb-4">
                                                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                            </svg>
                                                        </div>
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                                            Delete Forwarding Rule
                                                        </h3>
                                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                            Delete rule for <span class="font-semibold">{{ $forwarding->domain }}</span>?
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex flex-col space-y-2">
                                                    <form action="{{ route('user.vps.domain-forwarding.destroy', [$natVps, $forwarding]) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            Delete
                                                        </button>
                                                    </form>
                                                    <button type="button" 
                                                            @click="confirmDelete = false"
                                                            class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
