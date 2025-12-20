<?php

namespace App\Providers;

use App\Services\Virtualizor\Contracts\VirtualizorServiceInterface;
use App\Services\Virtualizor\VirtualizorService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register VirtualizorService as a singleton
        $this->app->singleton(VirtualizorServiceInterface::class, VirtualizorService::class);
        
        // Also bind the concrete class for direct injection
        $this->app->singleton(VirtualizorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Include the Virtualizor enduser.php library
        require_once app_path('Libraries/Virtualizor/enduser.php');
        
        // Configure rate limiters
        $this->configureRateLimiting();
    }
    
    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Login attempts: 5 per minute per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
        
        // VPS power actions: 10 per minute per user
        RateLimiter::for('vps-actions', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
        
        // Two-factor authentication attempts: 5 per minute per session
        // Requirements: 7.5 - Rate limiting to prevent brute force attacks
        RateLimiter::for('two-factor', function (Request $request) {
            // Use session ID for rate limiting during 2FA challenge
            // This prevents brute force attacks on the 2FA verification
            $sessionId = $request->session()->getId();
            $userId = $request->session()->get('two_factor:user_id');
            
            return Limit::perMinute(5)
                ->by($userId ? "2fa:{$userId}" : "2fa:session:{$sessionId}")
                ->response(function () {
                    return redirect()->back()
                        ->with('error', __('app.2fa_rate_limited'));
                });
        });
        
        // General API: 60 per minute per user
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
