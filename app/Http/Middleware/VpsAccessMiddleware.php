<?php

namespace App\Http\Middleware;

use App\Models\NatVps;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to verify user has access to a specific VPS.
 * 
 * - Admin users have access to all VPS instances
 * - Regular users can only access VPS instances assigned to them
 * - Unassigned VPS instances are accessible only by admins
 * 
 * **Validates: Requirements 4.3**
 */
class VpsAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // User must be authenticated
        if (!$user) {
            abort(403, 'Access denied. Authentication required.');
        }

        // Admin users have full access to all VPS instances
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Get the NatVps from route parameter
        $natVps = $request->route('natVps');

        // If natVps is not resolved yet (could be an ID), resolve it
        if (!$natVps instanceof NatVps) {
            $natVps = NatVps::find($natVps);
        }

        // If VPS doesn't exist, let the controller handle 404
        if (!$natVps) {
            return $next($request);
        }

        // Check if VPS is assigned to the current user
        if ($natVps->user_id !== $user->id) {
            abort(403, 'Access denied. You do not have permission to access this VPS.');
        }

        return $next($request);
    }
}
