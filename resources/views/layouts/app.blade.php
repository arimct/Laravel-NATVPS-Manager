<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NAT VPS Manager') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @auth
            @include('layouts.navigation')
        @endauth

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Toast Notifications Debug -->
    <!-- Session Data: success={{ session('success') ? 'YES' : 'NO' }}, error={{ session('error') ? 'YES' : 'NO' }}, warning={{ session('warning') ? 'YES' : 'NO' }} -->
    
    @if(session('success') || session('error') || session('warning') || session('info'))
    <script>
    (function() {
        var maxRetries = 50;
        var retryCount = 0;
        
        function showToasts() {
            retryCount++;
            
            if (typeof window.toast === 'undefined') {
                if (retryCount < maxRetries) {
                    setTimeout(showToasts, 100);
                } else {
                    console.error('Toast library not loaded after ' + maxRetries + ' retries');
                }
                return;
            }
            
            @if(session('success'))
                window.toast.success({!! json_encode(session('success')) !!});
            @endif

            @if(session('error'))
                window.toast.error({!! json_encode(session('error')) !!});
            @endif

            @if(session('warning'))
                window.toast.warning({!! json_encode(session('warning')) !!});
            @endif

            @if(session('info'))
                window.toast.info({!! json_encode(session('info')) !!});
            @endif
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showToasts);
        } else {
            setTimeout(showToasts, 50);
        }
    })();
    </script>
    @endif
</body>
</html>
