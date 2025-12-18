<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- VPS Summary Card (Requirement 10.4) -->
            <div class="mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Your VPS Instances</h3>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $assignedVpsCount }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">assigned to your account</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Access Links (Requirement 10.4) -->
            @if($assignedVps->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Quick Access</h3>
                            <a href="{{ route('user.vps.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                                View all →
                            </a>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($assignedVps as $vps)
                                <a href="{{ route('user.vps.show', $vps) }}" 
                                   class="block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $vps->hostname }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                VPS ID: {{ $vps->vps_id }}
                                            </p>
                                            @if($vps->server)
                                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                                    {{ $vps->server->name }}
                                                </p>
                                            @endif
                                        </div>
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No VPS assigned</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            You don't have any VPS instances assigned to your account yet.
                        </p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Please contact an administrator to get access to a VPS.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
