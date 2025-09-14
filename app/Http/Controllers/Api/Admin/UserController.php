<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use App\Services\UserDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    public function __construct(
        private readonly AuditLogRepository $auditLogRepository,
        private readonly UserDeletionService $userDeletionService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $role = $request->get('role');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->whereHas('cabinets', function ($q) use ($role) {
                $q->where('cabinet_user.is_owner', $role === 'owner');
            });
        }

        $users = $query->with(['cabinets', 'cabinetUsers'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['cabinets', 'cabinetUsers.permissions']);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Пользователь успешно обновлен',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $deletionResult = $this->userDeletionService->deleteUserWithCascade($user);

            return response()->json([
                'message' => 'Пользователь и все связанные данные успешно удалены',
                'deleted_data' => [
                    'cabinets_count' => count($deletionResult['cabinets']),
                    'notified_users_count' => count($deletionResult['notified_users']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при удалении пользователя: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get deletion summary for user.
     */
    public function getDeletionSummary(User $user): JsonResponse
    {
        $summary = $this->userDeletionService->getDeletionSummary($user);

        return response()->json([
            'summary' => $summary,
        ]);
    }

    /**
     * Get user's cabinets.
     */
    public function getUserCabinets(User $user): JsonResponse
    {
        $cabinets = $user->cabinets()->withPivot(['is_owner', 'created_at'])->get();

        return response()->json([
            'cabinets' => $cabinets->map(function ($cabinet) {
                return [
                    'id' => $cabinet->id,
                    'name' => $cabinet->name,
                    'description' => $cabinet->description,
                    'is_owner' => $cabinet->pivot->is_owner,
                    'joined_at' => $cabinet->pivot->created_at,
                ];
            }),
        ]);
    }

    /**
     * Get user's audit logs.
     */
    public function getUserAuditLogs(User $user, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $action = $request->get('action');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $logs = $this->auditLogRepository->getLogs([
            'user_id' => $user->id,
            'action' => $action,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], $perPage);

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
            'filters' => [
                'action' => $action,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
