<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NatVps;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * User Dashboard Controller
 * 
 * Implements Requirements: 10.4
 * - Display assigned VPS count for current user
 * - Provide quick access links to assigned VPS
 */
class DashboardController extends Controller
{
    /**
     * Display the user dashboard with VPS summary.
     * 
     * Requirements: 10.4
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Calculate assigned VPS count for current user (Requirement 10.4)
        $assignedVpsCount = NatVps::where('user_id', $user->id)->count();
        
        // Get assigned VPS for quick access links (Requirement 10.4)
        $assignedVps = NatVps::with('server')
            ->where('user_id', $user->id)
            ->orderBy('hostname')
            ->get();

        return view('user.dashboard', compact(
            'assignedVpsCount',
            'assignedVps'
        ));
    }
}
