<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NatVps;
use App\Models\Server;
use App\Models\User;
use App\Services\Virtualizor\Contracts\VirtualizorServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Admin Dashboard Controller
 * 
 * Implements Requirements: 10.1, 10.2, 10.3
 * - Display total counts of servers, NAT VPS instances, and users
 * - Show recent activity and system health indicators
 * - Highlight problematic servers on the dashboard
 */
class DashboardController extends Controller
{
    public function __construct(
        protected VirtualizorServiceInterface $virtualizorService
    ) {}

    /**
     * Display the admin dashboard with statistics.
     * 
     * Requirements: 10.1, 10.2, 10.3
     */
    public function index(): View
    {
        // Calculate total counts (Requirement 10.1)
        $totalServers = Server::count();
        $totalNatVps = NatVps::count();
        $totalUsers = User::count();
        
        // Get servers with connection issues (Requirement 10.3)
        $serversWithIssues = $this->getServersWithIssues();
        
        // Get recent activity summary (Requirement 10.2)
        $recentActivity = $this->getRecentActivity();
        
        // Get additional statistics
        $assignedVpsCount = NatVps::whereNotNull('user_id')->count();
        $unassignedVpsCount = NatVps::whereNull('user_id')->count();
        $activeServers = Server::where('is_active', true)->count();
        $inactiveServers = Server::where('is_active', false)->count();

        return view('admin.dashboard', compact(
            'totalServers',
            'totalNatVps',
            'totalUsers',
            'serversWithIssues',
            'recentActivity',
            'assignedVpsCount',
            'unassignedVpsCount',
            'activeServers',
            'inactiveServers'
        ));
    }


    /**
     * Get servers that have connection issues.
     * 
     * A server is considered to have issues if:
     * - It's marked as active but hasn't been checked recently
     * - The last connection test failed
     * 
     * Requirements: 10.3
     */
    protected function getServersWithIssues(): array
    {
        $issues = [];
        
        // Get active servers that haven't been checked in the last 24 hours
        $staleServers = Server::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('last_checked')
                    ->orWhere('last_checked', '<', now()->subHours(24));
            })
            ->get();

        foreach ($staleServers as $server) {
            $issues[] = [
                'server' => $server,
                'issue' => $server->last_checked 
                    ? 'Not checked in over 24 hours' 
                    : 'Never checked',
                'severity' => 'warning',
            ];
        }

        // Test connection for servers that need checking
        $serversToCheck = Server::where('is_active', true)
            ->whereNotNull('last_checked')
            ->where('last_checked', '>=', now()->subHours(24))
            ->get();

        foreach ($serversToCheck as $server) {
            try {
                $result = $this->virtualizorService->testConnection($server);
                if (!$result->success) {
                    $issues[] = [
                        'server' => $server,
                        'issue' => $result->message ?? 'Connection failed',
                        'severity' => 'error',
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Dashboard server check failed', [
                    'server_id' => $server->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't add to issues for transient errors during dashboard load
            }
        }

        return $issues;
    }

    /**
     * Get recent activity summary.
     * 
     * Requirements: 10.2
     */
    protected function getRecentActivity(): array
    {
        $activity = [];

        // Recent NAT VPS additions
        $recentVps = NatVps::with(['server', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentVps as $vps) {
            $activity[] = [
                'type' => 'vps_created',
                'message' => "NAT VPS '{$vps->hostname}' was added",
                'details' => $vps->server ? "on server {$vps->server->name}" : '',
                'timestamp' => $vps->created_at,
            ];
        }

        // Recent user registrations
        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentUsers as $user) {
            $activity[] = [
                'type' => 'user_created',
                'message' => "User '{$user->name}' was created",
                'details' => $user->email,
                'timestamp' => $user->created_at,
            ];
        }

        // Recent server additions
        $recentServers = Server::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentServers as $server) {
            $activity[] = [
                'type' => 'server_created',
                'message' => "Server '{$server->name}' was added",
                'details' => $server->ip_address,
                'timestamp' => $server->created_at,
            ];
        }

        // Sort by timestamp and limit to 10 most recent
        usort($activity, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        
        return array_slice($activity, 0, 10);
    }
}
