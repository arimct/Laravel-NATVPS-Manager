<?php

namespace App\Http\Controllers\User;

use App\Enums\DomainProtocol;
use App\Http\Controllers\Controller;
use App\Models\DomainForwarding;
use App\Models\NatVps;
use App\Services\Virtualizor\Contracts\VirtualizorServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

/**
 * Controller for managing domain forwarding (VDF) rules.
 * 
 * Implements Requirements: 7.1, 7.2, 7.3, 7.4, 7.5
 */
class DomainForwardingController extends Controller
{
    public function __construct(
        protected VirtualizorServiceInterface $virtualizorService
    ) {}

    /**
     * Display a listing of VDF rules for a VPS.
     * 
     * Requirements: 7.4 - Retrieve and display all VDF records for the VPS
     */
    public function index(NatVps $natVps): View
    {
        $apiOffline = false;
        $apiForwardings = [];

        // Sync local records with Virtualizor API
        if ($natVps->server) {
            try {
                $apiForwardings = $this->virtualizorService->getDomainForwarding(
                    $natVps->server,
                    $natVps->vps_id
                );

                // Sync local database with API data
                $this->syncForwardingsFromApi($natVps, $apiForwardings);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch domain forwarding from API', [
                    'nat_vps_id' => $natVps->id,
                    'error' => $e->getMessage(),
                ]);
                $apiOffline = true;
            }
        } else {
            $apiOffline = true;
        }

        // Reload the forwardings after sync
        $natVps->load('domainForwardings');

        return view('user.vps.domain-forwarding.index', [
            'natVps' => $natVps,
            'domainForwardings' => $natVps->domainForwardings,
            'apiOffline' => $apiOffline,
            'protocols' => DomainProtocol::cases(),
        ]);
    }

    /**
     * Store a newly created domain forwarding rule.
     * 
     * Requirements: 7.1 - Create HTTP/HTTPS forwarding rules via Virtualizor VDF API
     */
    public function store(Request $request, NatVps $natVps): RedirectResponse
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'protocol' => ['required', new Enum(DomainProtocol::class)],
            'source_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'destination_port' => ['required', 'integer', 'min:1', 'max:65535'],
        ]);

        if (!$natVps->server) {
            return redirect()
                ->route('user.vps.domain-forwarding.index', $natVps)
                ->with('error', 'Cannot create forwarding rule: VPS has no associated server.');
        }

        try {
            $result = $this->virtualizorService->createDomainForwarding(
                $natVps->server,
                $natVps->vps_id,
                $validated
            );

            if ($result->success) {
                // Create local record
                DomainForwarding::create([
                    'nat_vps_id' => $natVps->id,
                    'virtualizor_record_id' => $result->data['record_id'] ?? null,
                    'domain' => $validated['domain'],
                    'protocol' => $validated['protocol'],
                    'source_port' => $validated['source_port'],
                    'destination_port' => $validated['destination_port'],
                ]);

                return redirect()
                    ->route('user.vps.domain-forwarding.index', $natVps)
                    ->with('success', 'Domain forwarding rule created successfully.');
            }

            // Requirements: 7.5 - Display error message from API
            return redirect()
                ->route('user.vps.domain-forwarding.index', $natVps)
                ->with('error', $result->message ?? 'Failed to create domain forwarding rule.');
        } catch (\Exception $e) {
            Log::error('Failed to create domain forwarding', [
                'nat_vps_id' => $natVps->id,
                'data' => $validated,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('user.vps.domain-forwarding.index', $natVps)
                ->with('error', 'Failed to create domain forwarding rule: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified domain forwarding rule.
     * 
     * Requirements: 7.3 - Remove the rule via Virtualizor API
     */
    public function destroy(NatVps $natVps, DomainForwarding $domainForwarding): RedirectResponse
    {
        // Ensure the forwarding belongs to this VPS
        if ($domainForwarding->nat_vps_id !== $natVps->id) {
            abort(403, 'This forwarding rule does not belong to this VPS.');
        }

        if (!$natVps->server) {
            return redirect()
                ->route('user.vps.domain-forwarding.index', $natVps)
                ->with('error', 'Cannot delete forwarding rule: VPS has no associated server.');
        }

        try {
            // Only call API if we have a virtualizor_record_id
            if ($domainForwarding->virtualizor_record_id) {
                $result = $this->virtualizorService->deleteDomainForwarding(
                    $natVps->server,
                    $natVps->vps_id,
                    $domainForwarding->virtualizor_record_id
                );

                if (!$result->success) {
                    // Requirements: 7.5 - Display error message from API
                    return redirect()
                        ->route('user.vps.domain-forwarding.index', $natVps)
                        ->with('error', $result->message ?? 'Failed to delete domain forwarding rule.');
                }
            }

            // Delete local record
            $domainForwarding->delete();

            return redirect()
                ->route('user.vps.domain-forwarding.index', $natVps)
                ->with('success', 'Domain forwarding rule deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete domain forwarding', [
                'nat_vps_id' => $natVps->id,
                'forwarding_id' => $domainForwarding->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('user.vps.domain-forwarding.index', $natVps)
                ->with('error', 'Failed to delete domain forwarding rule: ' . $e->getMessage());
        }
    }

    /**
     * Sync local domain forwarding records with Virtualizor API data.
     * 
     * This ensures local database stays in sync with the actual VDF rules
     * configured in Virtualizor.
     */
    protected function syncForwardingsFromApi(NatVps $natVps, array $apiForwardings): void
    {
        if (empty($apiForwardings)) {
            return;
        }

        $existingRecordIds = $natVps->domainForwardings()
            ->whereNotNull('virtualizor_record_id')
            ->pluck('virtualizor_record_id')
            ->toArray();

        foreach ($apiForwardings as $apiRecord) {
            $recordId = $apiRecord['id'] ?? $apiRecord['vdf_id'] ?? null;
            
            if (!$recordId) {
                continue;
            }

            // Skip if we already have this record
            if (in_array($recordId, $existingRecordIds)) {
                continue;
            }

            // Create local record for API record we don't have
            DomainForwarding::create([
                'nat_vps_id' => $natVps->id,
                'virtualizor_record_id' => $recordId,
                'domain' => $apiRecord['src_hostname'] ?? $apiRecord['domain'] ?? '',
                'protocol' => $apiRecord['protocol'] ?? 'http',
                'source_port' => $apiRecord['src_port'] ?? $apiRecord['source_port'] ?? 80,
                'destination_port' => $apiRecord['dest_port'] ?? $apiRecord['destination_port'] ?? 80,
            ]);
        }
    }
}
