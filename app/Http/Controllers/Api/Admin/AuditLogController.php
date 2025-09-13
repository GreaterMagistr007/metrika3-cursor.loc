<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\AuditLogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogRepository $auditLogRepository
    ) {}

    /**
     * Get audit logs with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'user_id',
                'cabinet_id',
                'event',
                'subject_type',
                'subject_id',
                'date_from',
                'date_to',
            ]);

            $perPage = (int) $request->input('per_page', 15);
            $perPage = min($perPage, 100); // Limit to 100 per page

            $logs = $this->auditLogRepository->getLogs($filters, $perPage);

            return response()->json([
                'logs' => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ],
                'filters' => $filters,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch audit logs', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка получения логов аудита',
                'error_code' => 'AUDIT_LOGS_FETCH_FAILED'
            ], 500);
        }
    }

    /**
     * Get audit log statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'user_id',
                'cabinet_id',
                'date_from',
                'date_to',
            ]);

            $statistics = $this->auditLogRepository->getStatistics($filters);

            return response()->json([
                'statistics' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch audit log statistics', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка получения статистики логов аудита',
                'error_code' => 'AUDIT_STATISTICS_FETCH_FAILED'
            ], 500);
        }
    }

    /**
     * Get recent audit logs.
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->input('limit', 10);
            $limit = min($limit, 50); // Limit to 50

            $logs = $this->auditLogRepository->getRecentLogs($limit);

            return response()->json([
                'logs' => $logs
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch recent audit logs', [
                'limit' => $request->input('limit'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка получения последних логов аудита',
                'error_code' => 'RECENT_AUDIT_LOGS_FETCH_FAILED'
            ], 500);
        }
    }
}
