<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class AuditLogRepository
{
    /**
     * Log an audit event.
     */
    public function log(array $data): AuditLog
    {
        return AuditLog::create([
            'user_id' => $data['user_id'] ?? null,
            'cabinet_id' => $data['cabinet_id'] ?? null,
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'event' => $data['event'],
            'description' => $data['description'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Get audit logs with filtering and pagination.
     */
    public function getLogs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = AuditLog::with(['user', 'cabinet', 'subject']);

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (isset($filters['cabinet_id'])) {
            $query->forCabinet($filters['cabinet_id']);
        }

        if (isset($filters['event'])) {
            $query->forEvent($filters['event']);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->dateRange($filters['date_from'], $filters['date_to']);
        }

        if (isset($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        // Order by created_at desc
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get audit logs for a specific user.
     */
    public function getLogsForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['user_id'] = $userId;
        return $this->getLogs($filters, $perPage);
    }

    /**
     * Get audit logs for a specific cabinet.
     */
    public function getLogsForCabinet(int $cabinetId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['cabinet_id'] = $cabinetId;
        return $this->getLogs($filters, $perPage);
    }

    /**
     * Get recent audit logs.
     */
    public function getRecentLogs(int $limit = 10): Collection
    {
        return AuditLog::with(['user', 'cabinet', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs count by event type.
     */
    public function getEventCounts(array $filters = []): array
    {
        $query = AuditLog::query();

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (isset($filters['cabinet_id'])) {
            $query->forCabinet($filters['cabinet_id']);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->dateRange($filters['date_from'], $filters['date_to']);
        }

        return $query->selectRaw('event, COUNT(*) as count')
            ->groupBy('event')
            ->pluck('count', 'event')
            ->toArray();
    }

    /**
     * Get audit logs statistics.
     */
    public function getStatistics(array $filters = []): array
    {
        $query = AuditLog::query();

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (isset($filters['cabinet_id'])) {
            $query->forCabinet($filters['cabinet_id']);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->dateRange($filters['date_from'], $filters['date_to']);
        }

        return [
            'total_logs' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'unique_cabinets' => $query->distinct('cabinet_id')->count('cabinet_id'),
            'event_counts' => $this->getEventCounts($filters),
        ];
    }
}
