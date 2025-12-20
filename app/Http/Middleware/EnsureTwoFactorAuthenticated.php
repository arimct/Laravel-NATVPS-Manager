<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure 2FA verification is complete for users with 2FA enabled.
 * 
 * Requirements: 2.5
 * WHILE a user has not completed 2FA verification THEN the TwoFactorAuthSystem 
 * SHALL prevent access to protected routes.
 */
class EnsureTwoFactorAuthenticated
{
    /**
     * The URI path for the 2FA challenge page.
     */
    protected string $challengePath = '/two-factor/challenge';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If no user is authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }

        // If user has 2FA enabled but session is not verified, redirect to challenge
        if ($user->hasTwoFactorEnabled() && !$this->hasCompletedTwoFactorAuthentication($request)) {
            // Store the user ID in session for the challenge page
            $request->session()->put('two_factor:user_id', $user->id);
            
            // Log the user out (they need to complete 2FA first)
            auth()->logout();
            
            return redirect($this->challengePath);
        }

        return $next($request);
    }

    /**
     * Check if the user has completed 2FA authentication in this session.
     */
    protected function hasCompletedTwoFactorAuthentication(Request $request): bool
    {
        return $request->session()->get('two_factor:authenticated', false) === true;
    }
}
