<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Display a listing of audit logs with filters.
     * Requirements: 7.1, 7.2, 7.3, 7.4
     */
    public function index(Request $request): View
    {
        $filters = [];

        // Filter by user
        if ($request->filled('user_id')) {
            $filters['user_id'] = $request->input('user_id');
        }

        // Filter by action type
        if ($request->filled('action')) {
            $filters['action'] = $request->input('action');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->input('date_from') . ' 00:00:00';
        }

        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->input('date_to') . ' 23:59:59';
        }

        $logs = $this->auditLogService->getLogs($filters, 25);
        $users = User::orderBy('name')->get();
        $actions = $this->auditLogService->getAvailableActions();

        return view('admin.audit-logs.index', compact('logs', 'users', 'actions', 'filters'));
    }

    /**
     * Display the specified audit log entry.
     * Requirements: 7.5
     */
    public function show(AuditLog $auditLog): View
    {
        return view('admin.audit-logs.show', compact('auditLog'));
    }

    /**
     * Export audit logs as CSV.
     * Requirements: 7.1, 7.2, 7.3, 7.4
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = [];

        if ($request->filled('user_id')) {
            $filters['user_id'] = $request->input('user_id');
        }

        if ($request->filled('action')) {
            $filters['action'] = $request->input('action');
        }

        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->input('date_from') . ' 00:00:00';
        }

        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->input('date_to') . ' 23:59:59';
        }

        $filename = 'audit-logs-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');

            // CSV header
            fputcsv($handle, [
                'ID',
                'Action',
                'Actor ID',
                'Actor Type',
                'Actor Name',
                'Subject ID',
                'Subject Type',
                'IP Address',
                'User Agent',
                'Properties',
                'Created At',
            ]);

            // Get all logs without pagination for export
            $query = AuditLog::query();

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

            if (!empty($filters['action'])) {
                $query->where('action', $filters['action']);
            }

            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }

            $query->orderBy('created_at', 'desc')
                  ->chunk(100, function ($logs) use ($handle) {
                      foreach ($logs as $log) {
                          $actorName = '';
                          if ($log->actor_type === User::class && $log->actor_id) {
                              $actor = User::find($log->actor_id);
                              $actorName = $actor?->name ?? '';
                          }

                          fputcsv($handle, [
                              $log->id,
                              $log->action,
                              $log->actor_id,
                              $log->actor_type,
                              $actorName,
                              $log->subject_id,
                              $log->subject_type,
                              $log->ip_address,
                              $log->user_agent,
                              json_encode($log->properties),
                              $log->created_at?->toIso8601String(),
                          ]);
                      }
                  });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
