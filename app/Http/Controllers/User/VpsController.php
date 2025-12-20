<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NatVps;
use App\Services\GeoLocation\GeoLocationService;
use App\Services\MailService;
use App\Services\Virtualizor\Contracts\VirtualizorServiceInterface;
use App\Services\Virtualizor\DTOs\VpsInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controller for user VPS viewing and power actions.
 * 
 * Implements Requirements: 5.1, 5.2, 5.3, 5.4, 6.1, 6.2, 6.3, 6.4, 6.5
 */
class VpsController extends Controller
{
    public function __construct(
        protected VirtualizorServiceInterface $virtualizorService,
        protected GeoLocationService $geoLocationService
    ) {}

    /**
     * Display a listing of VPS instances assigned to the current user.
     * 
     * Requirements: 5.1 - Retrieve VPS details from Virtualizor API
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get VPS instances assigned to the current user
        // Admin users see all VPS instances
        if ($user->isAdmin()) {
            $vpsList = NatVps::with('server', 'user')->get();
        } else {
            $vpsList = NatVps::with('server')
                ->where('user_id', $user->id)
                ->get();
        }

        // Fetch live data from API for each VPS
        $vpsWithSpecs = [];
        $apiErrors = [];

        foreach ($vpsList as $natVps) {
            $vpsData = [
                'natVps' => $natVps,
                'liveInfo' => null,
                'apiOffline' => false,
            ];

            if ($natVps->server) {
                try {
                    $liveInfo = $this->virtualizorService->getVpsInfo(
                        $natVps->server,
                        $natVps->vps_id
                    );

                    if ($liveInfo) {
                        $vpsData['liveInfo'] = $liveInfo;
                        // Update cached specs
                        $this->updateCachedSpecs($natVps, $liveInfo);
                    } else {
                        $vpsData['apiOffline'] = true;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch VPS info', [
                        'nat_vps_id' => $natVps->id,
                        'error' => $e->getMessage(),
                    ]);
                    $vpsData['apiOffline'] = true;
                    $apiErrors[] = $natVps->hostname;
                }
            }

            $vpsWithSpecs[] = $vpsData;
        }

        // Flash warning if there are API errors
        if (!empty($apiErrors)) {
            session()->flash('warning', 'API connection issues for: ' . implode(', ', $apiErrors) . '. Showing cached data where available.');
        }

        return view('user.vps.index', [
            'vpsWithSpecs' => $vpsWithSpecs,
            'apiErrors' => $apiErrors,
        ]);
    }

    /**
     * Display the specified VPS with details from API.
     * 
     * Requirements: 5.2, 5.3, 5.4 - Show VPS specs and SSH credentials
     */
    public function show(NatVps $natVps): View
    {
        // Load server relation for SSH command display
        $natVps->load('server');

        $liveInfo = null;
        $apiOffline = false;

        if ($natVps->server) {
            try {
                $liveInfo = $this->virtualizorService->getVpsInfo(
                    $natVps->server,
                    $natVps->vps_id
                );

                if ($liveInfo) {
                    $this->updateCachedSpecs($natVps, $liveInfo);
                } else {
                    $apiOffline = true;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch VPS info for show', [
                    'nat_vps_id' => $natVps->id,
                    'error' => $e->getMessage(),
                ]);
                $apiOffline = true;
            }
        } else {
            $apiOffline = true;
        }

        // Fetch server location data if not cached
        if ($natVps->server && !$natVps->server->location_data) {
            $this->geoLocationService->getLocationForServer($natVps->server);
            $natVps->load('server'); // Reload to get updated location
        }

        // Flash warning if API is offline
        if ($apiOffline) {
            $message = 'API is currently unavailable. Showing cached data.';
            if ($natVps->specs_cached_at) {
                $message .= ' Last updated: ' . $natVps->specs_cached_at->diffForHumans();
            }
            session()->flash('warning', $message);
        }

        // Resource usage will be loaded via AJAX for better page performance
        return view('user.vps.show', [
            'natVps' => $natVps,
            'liveInfo' => $liveInfo,
            'apiOffline' => $apiOffline,
        ]);
    }

    /**
     * Start the specified VPS.
     * 
     * Requirements: 6.1 - Call Virtualizor API start method
     */
    public function start(NatVps $natVps): RedirectResponse
    {
        return $this->performPowerAction($natVps, 'start');
    }

    /**
     * Stop the specified VPS.
     * 
     * Requirements: 6.2 - Call Virtualizor API stop method
     */
    public function stop(NatVps $natVps): RedirectResponse
    {
        return $this->performPowerAction($natVps, 'stop');
    }

    /**
     * Restart the specified VPS.
     * 
     * Requirements: 6.3 - Call Virtualizor API restart method
     */
    public function restart(NatVps $natVps): RedirectResponse
    {
        return $this->performPowerAction($natVps, 'restart');
    }

    /**
     * Power off the specified VPS.
     * 
     * Requirements: 6.4 - Call Virtualizor API poweroff method
     */
    public function poweroff(NatVps $natVps): RedirectResponse
    {
        return $this->performPowerAction($natVps, 'poweroff');
    }

    /**
     * Perform a power action on the VPS.
     * 
     * Requirements: 6.5 - Display error message with details from API response
     */
    protected function performPowerAction(NatVps $natVps, string $action): RedirectResponse
    {
        if (!$natVps->server) {
            return redirect()
                ->route('user.vps.show', $natVps)
                ->with('error', __('app.vps_no_server'));
        }

        try {
            $result = match ($action) {
                'start' => $this->virtualizorService->startVps($natVps->server, $natVps->vps_id),
                'stop' => $this->virtualizorService->stopVps($natVps->server, $natVps->vps_id),
                'restart' => $this->virtualizorService->restartVps($natVps->server, $natVps->vps_id),
                'poweroff' => $this->virtualizorService->poweroffVps($natVps->server, $natVps->vps_id),
                default => throw new \InvalidArgumentException("Unknown action: {$action}"),
            };

            if ($result->success) {
                // Send power action notification to VPS owner
                if ($natVps->user) {
                    $mailService = app(MailService::class);
                    $mailService->sendVpsPowerAction($natVps->user, $natVps, $action, auth()->user()?->name ?? 'System');
                }
                
                $successMessage = match ($action) {
                    'start' => __('app.vps_started'),
                    'stop' => __('app.vps_stopped'),
                    'restart' => __('app.vps_restarted'),
                    'poweroff' => __('app.vps_powered_off'),
                    default => __('app.success'),
                };
                
                return redirect()
                    ->route('user.vps.show', $natVps)
                    ->with('success', $successMessage);
            }

            // Parse API error for user-friendly message
            $errorMessage = $this->parseApiError($result->message, $action);
            
            return redirect()
                ->route('user.vps.show', $natVps)
                ->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error("Failed to {$action} VPS", [
                'nat_vps_id' => $natVps->id,
                'error' => $e->getMessage(),
            ]);

            // Provide user-friendly error based on exception type
            $errorMessage = $this->getExceptionMessage($e, $action);

            return redirect()
                ->route('user.vps.show', $natVps)
                ->with('error', $errorMessage);
        }
    }

    /**
     * Parse API error message into user-friendly format.
     */
    protected function parseApiError(?string $message, string $action): string
    {
        if (empty($message)) {
            return "Unable to {$action} VPS. Please try again or contact support if the issue persists.";
        }

        // Common error patterns
        $patterns = [
            '/already running/i' => 'VPS is already running. No action needed.',
            '/already stopped/i' => 'VPS is already stopped. No action needed.',
            '/not running/i' => 'VPS is not currently running.',
            '/permission denied/i' => 'You do not have permission to perform this action.',
            '/timeout/i' => 'The operation timed out. Please try again.',
            '/connection refused/i' => 'Unable to connect to the server. Please try again later.',
        ];

        foreach ($patterns as $pattern => $friendlyMessage) {
            if (preg_match($pattern, $message)) {
                return $friendlyMessage;
            }
        }

        return "Unable to {$action} VPS: {$message}";
    }

    /**
     * Get user-friendly message from exception.
     */
    protected function getExceptionMessage(\Exception $e, string $action): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'Connection refused') || str_contains($message, 'Could not connect')) {
            return 'Unable to connect to the server. The server may be temporarily unavailable. Please try again later.';
        }

        if (str_contains($message, 'timed out') || str_contains($message, 'timeout')) {
            return 'The operation timed out. The server may be busy. Please try again in a few moments.';
        }

        if (str_contains($message, 'authentication') || str_contains($message, 'unauthorized')) {
            return 'Authentication failed. Please contact support.';
        }

        return "An unexpected error occurred while trying to {$action} your VPS. Please try again or contact support.";
    }

    /**
     * Update cached specs for a NAT VPS.
     * 
     * Requirements: 5.3 - Cache data for offline display
     */
    protected function updateCachedSpecs(NatVps $natVps, VpsInfo $vpsInfo): void
    {
        $natVps->update([
            'cached_specs' => $vpsInfo->toArray(),
            'specs_cached_at' => now(),
        ]);
    }

    /**
     * Get resource usage data for a VPS (AJAX endpoint).
     */
    public function resourceUsage(NatVps $natVps): JsonResponse
    {
        if (!$natVps->server) {
            return response()->json([
                'success' => false,
                'message' => 'VPS has no associated server.',
            ], 400);
        }

        try {
            $resourceUsage = $this->virtualizorService->getResourceUsage($natVps->server, $natVps->vps_id);

            if (!$resourceUsage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch resource usage data.',
                ], 503);
            }

            return response()->json([
                'success' => true,
                'data' => $resourceUsage->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch resource usage', [
                'nat_vps_id' => $natVps->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch resource usage.',
            ], 500);
        }
    }
}
