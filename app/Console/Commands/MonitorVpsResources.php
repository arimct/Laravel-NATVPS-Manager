<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\NatVps;
use App\Models\Setting;
use App\Models\User;
use App\Services\MailService;
use App\Services\SettingService;
use App\Services\Virtualizor\VirtualizorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorVpsResources extends Command
{
    protected $signature = 'vps:monitor-resources';
    protected $description = 'Monitor VPS resource usage and send warnings if thresholds are exceeded';

    public function handle(
        VirtualizorService $virtualizorService,
        MailService $mailService,
        SettingService $settingService
    ): int {
        // Check if monitoring is enabled
        if (!Setting::get('resource_monitor_enabled', false)) {
            $this->info('Resource monitoring is disabled.');
            return self::SUCCESS;
        }

        // Check if email is enabled
        if (!$settingService->isMailEnabled()) {
            $this->warn('Email is disabled. Skipping resource monitoring.');
            return self::SUCCESS;
        }

        // Check if resource warning notification is enabled
        if (!$settingService->isNotificationEnabled('resource_warning')) {
            $this->info('Resource warning notifications are disabled.');
            return self::SUCCESS;
        }

        $this->info('Starting VPS resource monitoring...');

        // Get thresholds
        $cpuThreshold = Setting::get('resource_warning_cpu_threshold', 90);
        $ramThreshold = Setting::get('resource_warning_ram_threshold', 90);
        $diskThreshold = Setting::get('resource_warning_disk_threshold', 90);
        $cooldownMinutes = Setting::get('resource_warning_cooldown', 60);

        $this->info("Thresholds - CPU: {$cpuThreshold}%, RAM: {$ramThreshold}%, Disk: {$diskThreshold}%");
        $this->info("Warning cooldown: {$cooldownMinutes} minutes");

        // Get all VPS with active servers (including unassigned ones)
        $vpsList = NatVps::with(['server', 'user'])
            ->whereHas('server', function ($query) {
                $query->where('is_active', true);
            })
            ->get();

        // Get admin users for unassigned VPS notifications
        $adminUsers = User::where('role', UserRole::Admin)->get();

        $this->info("Checking {$vpsList->count()} VPS instances...");
        $this->info("Admin recipients: {$adminUsers->count()}");

        $checked = 0;
        $warnings = 0;
        $errors = 0;

        foreach ($vpsList as $vps) {
            $ownerInfo = $vps->user ? $vps->user->name : 'Unassigned';
            $this->line("Checking: {$vps->hostname} ({$ownerInfo})");

            // Check cooldown
            if ($vps->last_resource_warning_at) {
                $minutesSinceLastWarning = now()->diffInMinutes($vps->last_resource_warning_at);
                if ($minutesSinceLastWarning < $cooldownMinutes) {
                    $this->line("  → Skipped (cooldown: {$minutesSinceLastWarning}/{$cooldownMinutes} min)");
                    continue;
                }
            }

            try {
                $resourceUsage = $virtualizorService->getResourceUsage($vps->server, $vps->vps_id);

                if (!$resourceUsage) {
                    $this->warn("  → Failed to get resource usage");
                    $errors++;
                    continue;
                }

                $checked++;
                $cpuUsage = $resourceUsage->cpu?->percent ?? 0;
                $ramUsage = $resourceUsage->ram?->percent ?? 0;
                $diskUsage = $resourceUsage->disk?->percent ?? 0;

                $this->line("  → CPU: {$cpuUsage}%, RAM: {$ramUsage}%, Disk: {$diskUsage}%");

                // Check if any threshold is exceeded
                $warningTypes = [];
                if ($cpuUsage >= $cpuThreshold) {
                    $warningTypes[] = 'cpu';
                }
                if ($ramUsage >= $ramThreshold) {
                    $warningTypes[] = 'ram';
                }
                if ($diskUsage >= $diskThreshold) {
                    $warningTypes[] = 'disk';
                }

                if (!empty($warningTypes)) {
                    $warningType = implode(', ', array_map('strtoupper', $warningTypes));
                    $this->warn("  → WARNING: {$warningType} threshold exceeded!");

                    $resourceData = [
                        'cpu' => $cpuUsage,
                        'ram' => $ramUsage,
                        'disk' => $diskUsage,
                    ];

                    // Pass all warning types instead of just primary
                    $allWarnings = implode(', ', array_map(fn($t) => ucfirst($t), $warningTypes));
                    $sent = false;

                    // Send to VPS owner if assigned
                    if ($vps->user) {
                        $sent = $mailService->sendResourceWarning($vps->user, $vps, $resourceData, $allWarnings);
                        if ($sent) {
                            $this->info("  → Warning email sent to user: {$vps->user->email}");
                            sleep(5); // Delay 5 seconds between emails
                        }
                    }

                    // Also send to all admins
                    foreach ($adminUsers as $admin) {
                        // Skip if admin is also the VPS owner (already notified)
                        if ($vps->user && $vps->user->id === $admin->id) {
                            continue;
                        }
                        
                        $adminSent = $mailService->sendResourceWarning($admin, $vps, $resourceData, $allWarnings);
                        if ($adminSent) {
                            $this->info("  → Warning email sent to admin: {$admin->email}");
                            $sent = true;
                            sleep(5); // Delay 5 seconds between emails
                        }
                    }

                    if ($sent) {
                        // Update last warning timestamp
                        $vps->update(['last_resource_warning_at' => now()]);
                        $warnings++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("  → Error: {$e->getMessage()}");
                Log::error('Resource monitoring error', [
                    'vps_id' => $vps->id,
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        $this->newLine();
        $this->info("Monitoring complete!");
        $this->info("Checked: {$checked}, Warnings sent: {$warnings}, Errors: {$errors}");

        return self::SUCCESS;
    }
}
