<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Log an activity.
     *
     * @param string $action The action type (e.g., 'auth.login', 'user.created')
     * @param User|null $actor The user who performed the action
     * @param Model|null $subject The entity affected by the action
     * @param array $properties Additional properties (old/new values, metadata)
     * @param string|null $ipAddress The IP address (auto-detected if null)
     * @param string|null $userAgent The user agent (auto-detected if null)
     * @return AuditLog|null The created audit log entry, or null on failure
     */
    public function log(
        string $action,
        ?User $actor = null,
        ?Model $subject = null,
        array $properties = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ?AuditLog {
        try {
            // Auto-detect IP and user agent from request if not provided
            if ($ipAddress === null || $userAgent === null) {
                $request = request();
                if ($request instanceof Request) {
                    $ipAddress = $ipAddress ?? $request->ip();
                    $userAgent = $userAgent ?? $request->userAgent();
                }
            }

            return AuditLog::create([
                'action' => $action,
                'actor_id' => $actor?->id,
                'actor_type' => $actor ? get_class($actor) : null,
                'subject_id' => $subject?->id,
                'subject_type' => $subject ? get_class($subject) : null,
                'properties' => $this->serializeProperties($properties),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the primary operation
            Log::error('Failed to create audit log', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }


    /**
     * Serialize properties for storage.
     * Ensures old/new values are properly structured.
     *
     * @param array $properties
     * @return array
     */
    protected function serializeProperties(array $properties): array
    {
        // If properties already have old/new structure, return as-is
        if (isset($properties['old']) || isset($properties['new']) || isset($properties['metadata'])) {
            return $properties;
        }

        // Return properties as-is for simple metadata
        return $properties;
    }

    /**
     * Create properties array with old and new values for update operations.
     *
     * @param array $oldValues
     * @param array $newValues
     * @param array $metadata Additional metadata
     * @return array
     */
    public static function makeUpdateProperties(array $oldValues, array $newValues, array $metadata = []): array
    {
        $properties = [
            'old' => $oldValues,
            'new' => $newValues,
        ];

        if (!empty($metadata)) {
            $properties['metadata'] = $metadata;
        }

        return $properties;
    }

    /**
     * Create properties array with result status for action operations.
     *
     * @param string $result 'success' or 'failure'
     * @param string|null $error Error message if failure
     * @param array $metadata Additional metadata
     * @return array
     */
    public static function makeActionProperties(string $result, ?string $error = null, array $metadata = []): array
    {
        $properties = array_merge(['result' => $result], $metadata);

        if ($error !== null) {
            $properties['error'] = $error;
        }

        return $properties;
    }

    /**
     * Get audit logs with optional filters.
     * Results are always ordered by created_at descending (newest first).
     *
     * @param array $filters Available filters: user_id, action, date_from, date_to
     * @param int $perPage Number of results per page
     * @return LengthAwarePaginator
     */
    public function getLogs(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = AuditLog::query();

        // Filter by user (as actor or subject)
        if (!empty($filters['user_id'])) {
            $userId = $filters['user_id'];
            $userType = User::class;
            $query->where(function ($q) use ($userId, $userType) {
                $q->where(function ($subQ) use ($userId, $userType) {
                    $subQ->where('actor_id', $userId)
                         ->where('actor_type', $userType);
                })->orWhere(function ($subQ) use ($userId, $userType) {
                    $subQ->where('subject_id', $userId)
                         ->where('subject_type', $userType);
                });
            });
        }

        // Filter by action type
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        // Filter by date range - from
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        // Filter by date range - to
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Always order by created_at descending (newest first)
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get audit logs for a specific user (as actor or subject).
     * Results are always ordered by created_at descending (newest first).
     *
     * @param User $user The user to get logs for
     * @param int $perPage Number of results per page
     * @return LengthAwarePaginator
     */
    public function getLogsForUser(User $user, int $perPage = 25): LengthAwarePaginator
    {
        return $this->getLogs(['user_id' => $user->id], $perPage);
    }

    /**
     * Clean up old audit logs based on retention policy.
     *
     * @param int $retentionDays Number of days to retain logs
     * @return int Number of deleted records
     */
    public function cleanupOldLogs(int $retentionDays): int
    {
        $cutoffDate = now()->subDays($retentionDays);

        return AuditLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get all available action types from existing logs.
     *
     * @return array
     */
    public function getAvailableActions(): array
    {
        return AuditLog::distinct()->pluck('action')->toArray();
    }
}
