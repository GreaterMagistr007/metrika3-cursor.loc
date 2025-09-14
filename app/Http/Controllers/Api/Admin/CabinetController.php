<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CabinetResource;
use App\Models\Cabinet;
use App\Repositories\AuditLogRepository;
use App\Services\CabinetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CabinetController extends Controller
{
    public function __construct(
        private readonly CabinetService $cabinetService,
        private readonly AuditLogRepository $auditLogRepository
    ) {}

    /**
     * Display a listing of cabinets.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $ownerId = $request->get('owner_id');

        $query = Cabinet::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($ownerId) {
            $query->whereHas('users', function ($q) use ($ownerId) {
                $q->where('cabinet_user.is_owner', true)
                  ->where('users.id', $ownerId);
            });
        }

        $cabinets = $query->with(['users', 'owner'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'cabinets' => CabinetResource::collection($cabinets->items()),
            'pagination' => [
                'current_page' => $cabinets->currentPage(),
                'last_page' => $cabinets->lastPage(),
                'per_page' => $cabinets->perPage(),
                'total' => $cabinets->total(),
                'from' => $cabinets->firstItem(),
                'to' => $cabinets->lastItem(),
            ],
            'filters' => [
                'search' => $search,
                'owner_id' => $ownerId,
            ],
        ]);
    }

    /**
     * Display the specified cabinet.
     */
    public function show(Cabinet $cabinet): JsonResponse
    {
        $cabinet->load(['users', 'owner', 'users.permissions']);

        return response()->json([
            'cabinet' => new CabinetResource($cabinet),
        ]);
    }

    /**
     * Update the specified cabinet.
     */
    public function update(Request $request, Cabinet $cabinet): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
        ]);

        $cabinet->update($validated);

        return response()->json([
            'message' => 'Кабинет успешно обновлен',
            'cabinet' => new CabinetResource($cabinet->fresh()),
        ]);
    }

    /**
     * Remove the specified cabinet.
     */
    public function destroy(Cabinet $cabinet): JsonResponse
    {
        $cabinet->delete();

        return response()->json([
            'message' => 'Кабинет успешно удален',
        ]);
    }

    /**
     * Get cabinet's users.
     */
    public function getCabinetUsers(Cabinet $cabinet): JsonResponse
    {
        $users = $cabinet->users()->withPivot(['is_owner', 'created_at'])->get();

        return response()->json([
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'is_owner' => $user->pivot->is_owner,
                    'joined_at' => $user->pivot->created_at,
                ];
            }),
        ]);
    }

    /**
     * Get cabinet's audit logs.
     */
    public function getCabinetAuditLogs(Cabinet $cabinet, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $action = $request->get('action');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $logs = $this->auditLogRepository->getLogs([
            'cabinet_id' => $cabinet->id,
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

    /**
     * Transfer cabinet ownership.
     */
    public function transferOwnership(Request $request, Cabinet $cabinet): JsonResponse
    {
        $validated = $request->validate([
            'new_owner_id' => 'required|exists:users,id',
        ]);

        $newOwner = \App\Models\User::findOrFail($validated['new_owner_id']);

        // Check if new owner is already a member of the cabinet
        if (!$cabinet->users()->where('users.id', $newOwner->id)->exists()) {
            return response()->json([
                'message' => 'Пользователь не является участником кабинета',
                'error_code' => 'USER_NOT_MEMBER',
            ], 422);
        }

        $this->cabinetService->transferOwnership($cabinet, $newOwner);

        return response()->json([
            'message' => 'Права владения кабинетом успешно переданы',
            'cabinet' => new CabinetResource($cabinet->fresh()),
        ]);
    }
}
