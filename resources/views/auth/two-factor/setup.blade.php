<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('app.2fa_setup') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <!-- Instructions -->
                    <div class="mb-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('app.2fa_setup_instructions') }}
                        </p>
                    </div>

                    <!-- QR Code Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-4">{{ __('app.2fa_scan_qr') }}</h3>
                        <div class="flex justify-center p-4 bg-white rounded-lg">
                            <img src="{{ $qrCodeUrl }}" alt="QR Code" class="w-48 h-48">
                        </div>
                    </div>

                    <!-- Manual Entry Code -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-2">{{ __('app.2fa_manual_entry') }}</h3>
                        <div class="flex items-center space-x-2">
                            <code class="flex-1 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg font-mono text-sm break-all select-all">
                                {{ $secret }}
                            </code>
                            <button type="button" 
                                    onclick="copyToClipboard('{{ $secret }}')"
                                    class="px-3 py-2 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Verification Form -->
                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="code" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('app.2fa_enter_code') }}
                            </label>
                            <input id="code" 
                                   type="text" 
                                   name="code" 
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-center text-2xl tracking-widest"
                                   placeholder="000000"
                                   required 
                                   autofocus />
                            @error('code')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('app.2fa_enable') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                if (typeof window.toast !== 'undefined') {
                    window.toast.success('{{ __('app.copied') }}');
                }
            });
        }
    </script>
</x-app-layout>
