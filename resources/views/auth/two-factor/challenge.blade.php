<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ __('app.2fa_challenge_title') }}
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ __('app.2fa_challenge_desc') }}
        </p>
    </div>

    <!-- TOTP Code Form -->
    <form method="POST" action="{{ route('two-factor.verify') }}" id="totp-form">
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
                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('app.2fa_verify') }}
            </button>
        </div>
    </form>

    <!-- Divider -->
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                {{ __('app.or') }}
            </span>
        </div>
    </div>

    <!-- Recovery Code Toggle -->
    <button type="button" 
            onclick="toggleRecoveryForm()"
            id="toggle-recovery-btn"
            class="w-full text-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
        {{ __('app.2fa_use_recovery') }}
    </button>

    <!-- Recovery Code Form (Hidden by default) -->
    <form method="POST" action="{{ route('two-factor.recovery') }}" id="recovery-form" class="hidden mt-4">
        @csrf

        <div class="mb-4">
            <label for="recovery_code" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">
                {{ __('app.2fa_enter_recovery') }}
            </label>
            <input id="recovery_code" 
                   type="text" 
                   name="recovery_code" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 font-mono"
                   placeholder="XXXX-XXXX-XXXX"
                   required />
            @error('recovery_code')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" 
                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('app.2fa_verify') }}
            </button>
        </div>
    </form>

    <!-- Back to TOTP Toggle (Hidden by default) -->
    <button type="button" 
            onclick="toggleRecoveryForm()"
            id="toggle-totp-btn"
            class="hidden w-full text-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mt-4">
        {{ __('app.2fa_use_totp') }}
    </button>

    <script>
        function toggleRecoveryForm() {
            const totpForm = document.getElementById('totp-form');
            const recoveryForm = document.getElementById('recovery-form');
            const toggleRecoveryBtn = document.getElementById('toggle-recovery-btn');
            const toggleTotpBtn = document.getElementById('toggle-totp-btn');

            if (recoveryForm.classList.contains('hidden')) {
                // Show recovery form
                totpForm.classList.add('hidden');
                recoveryForm.classList.remove('hidden');
                toggleRecoveryBtn.classList.add('hidden');
                toggleTotpBtn.classList.remove('hidden');
                document.getElementById('recovery_code').focus();
            } else {
                // Show TOTP form
                totpForm.classList.remove('hidden');
                recoveryForm.classList.add('hidden');
                toggleRecoveryBtn.classList.remove('hidden');
                toggleTotpBtn.classList.add('hidden');
                document.getElementById('code').focus();
            }
        }
    </script>
</x-guest-layout>
