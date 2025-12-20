<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupAuditLogs extends Command
{
    protected $signature = 'audit:cleanup {--days= : Override retention days setting}';
    protected $description = 'Clean up old audit logs based on retention policy';

    public function handle(AuditLogService $auditLogService): int
    {
        // Get retention days from option or setting
        $retentionDays = $this->option('days') 
            ?? Setting::get('audit_log_retention_days', 90);

        if ($retentionDays <= 0) {
            $this->info('Audit log retention is disabled (retention days <= 0).');
            return self::SUCCESS;
        }

        $this->info("Cleaning up audit logs older than {$retentionDays} days...");

        try {
            $deletedCount = $auditLogService->cleanupOldLogs($retentionDays);

            $this->info("Successfully deleted {$deletedCount} old audit log entries.");

            Log::info('Audit log cleanup completed', [
                'retention_days' => $retentionDays,
                'deleted_count' => $deletedCount,
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to cleanup audit logs: ' . $e->getMessage());

            Log::error('Audit log cleanup failed', [
                'retention_days' => $retentionDays,
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}
