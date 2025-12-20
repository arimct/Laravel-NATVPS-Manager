<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('app.2fa_recovery_codes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- 2FA Status -->
                    <div class="mb-6 flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">
                                {{ __('app.2fa_enabled') }}
                            </p>
                        </div>
                    </div>

                    <!-- Warning if codes are low -->
                    @if($showWarning)
                        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        {{ __('app.2fa_codes_warning') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Recovery Codes Display (only shown after enable/regenerate) -->
                    @if($recoveryCodes)
                        <div class="mb-6">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start mb-4">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-600 dark:text-red-400">
                                            {{ __('app.2fa_recovery_codes_desc') }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($recoveryCodes as $code)
                                        <code class="p-2 bg-white dark:bg-gray-800 rounded text-sm font-mono text-center border border-gray-200 dark:border-gray-600">
                                            {{ $code }}
                                        </code>
                                    @endforeach
                                </div>

                                <div class="mt-4 flex justify-center">
                                    <button type="button" 
                                            onclick="copyAllCodes()"
                                            class="inline-flex items-center px-3 py-2 text-sm bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ __('app.copy') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Remaining codes count -->
                        <div class="mb-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('app.2fa_codes_remaining', ['count' => $remainingCount]) }}
                            </p>
                        </div>
                    @endif

                    <!-- Regenerate Recovery Codes -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium mb-4">{{ __('app.2fa_regenerate_codes') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('app.2fa_regenerate_desc') }}
                        </p>
                        
                        <form method="POST" action="{{ route('two-factor.recovery-codes.regenerate') }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="regenerate_password" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('app.2fa_confirm_password') }}
                                </label>
                                <input id="regenerate_password" 
                                       type="password" 
                                       name="password" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                       required />
                            </div>

                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('app.2fa_regenerate_codes') }}
                            </button>
                        </form>
                    </div>

                    <!-- Disable 2FA -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                        <h3 class="text-lg font-medium mb-4 text-red-600 dark:text-red-400">{{ __('app.2fa_disable') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('app.2fa_disable_desc') }}
                        </p>
                        
                        <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('{{ __('app.2fa_disable_confirm') }}')">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="disable_password" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('app.2fa_confirm_password') }}
                                </label>
                                <input id="disable_password" 
                                       type="password" 
                                       name="password" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                       required />
                            </div>

                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('app.2fa_disable') }}
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @if($recoveryCodes)
    <script>
        function copyAllCodes() {
            const codes = @json($recoveryCodes);
            const text = codes.join('\n');
            navigator.clipboard.writeText(text).then(function() {
                if (typeof window.toast !== 'undefined') {
                    window.toast.success('{{ __('app.copied') }}');
                }
            });
        }
    </script>
    @endif
</x-app-layout>
