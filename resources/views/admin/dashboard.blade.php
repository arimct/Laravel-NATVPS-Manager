<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('app.admin_dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Servers -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('app.total_servers') }}</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalServers }}</div>
                                        <div class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ $activeServers }} {{ __('app.active') }})</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3">
                        <a href="{{ route('admin.servers.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">{{ __('app.view_all') }} →</a>
                    </div>
                </div>

                <!-- Total VPS -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('app.total_vps') }}</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalNatVps }}</div>
                                        <div class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ $assignedVpsCount }} {{ __('app.assigned') }})</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3">
                        <a href="{{ route('admin.nat-vps.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">{{ __('app.view_all') }} →</a>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('app.total_users') }}</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalUsers }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3">
                        <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">{{ __('app.view_all') }} →</a>
                    </div>
                </div>

                <!-- Unassigned VPS -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('app.unassigned_vps') }}</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $unassignedVpsCount }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.awaiting_assignment') }}</span>
                    </div>
                </div>
            </div>

            @if(count($serversWithIssues) > 0)
            <div class="mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('app.server_issues') }}</h3>
                        <div class="space-y-3">
                            @foreach($serversWithIssues as $issue)
                            <div class="flex items-center justify-between p-3 rounded-lg {{ $issue['severity'] === 'error' ? 'bg-red-50 dark:bg-red-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20' }}">
                                <div class="flex items-center">
                                    <span class="flex-shrink-0 w-2 h-2 {{ $issue['severity'] === 'error' ? 'bg-red-500' : 'bg-yellow-500' }} rounded-full mr-3"></span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $issue['server']->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $issue['issue'] }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('admin.servers.edit', $issue['server']) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ __('app.view') }} →</a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('app.recent_activity') }}</h3>
                    @if(count($recentActivity) > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($recentActivity as $index => $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if($index < count($recentActivity) - 1)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full {{ $activity['type'] === 'vps_created' ? 'bg-green-500' : ($activity['type'] === 'user_created' ? 'bg-blue-500' : 'bg-indigo-500') }} flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $activity['message'] }}</p>
                                                @if($activity['details'])
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activity['details'] }}</p>
                                                @endif
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">{{ $activity['timestamp']->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.no_recent_activity') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
