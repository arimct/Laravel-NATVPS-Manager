{{-- Toast Notifications using Toastify --}}
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
        // Small delay to ensure Vite scripts are executed
        setTimeout(showToasts, 50);
    }
})();
</script>
@endif
