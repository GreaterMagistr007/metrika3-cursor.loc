<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\PermissionAssigned;
use App\Events\PermissionRevoked;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssignPermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Cabinet;
use App\Models\CabinetUser;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class CabinetUserPermissionController extends Controller
{
    /**
     * Get user permissions in cabinet.
     */
    public function index(Request $request, Cabinet $cabinet, User $user): JsonResponse
    {
        try {
            $currentUser = $request->user();

            // Check if current user has permission to view user permissions
            if (!$currentUser->hasPermissionInCabinet('user.view', $cabinet->id)) {
                return response()->json([
                    'message' => 'Недостаточно прав для просмотра прав пользователя',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS'
                ], 403);
            }

            // Get cabinet user relationship
            $cabinetUser = CabinetUser::where('cabinet_id', $cabinet->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$cabinetUser) {
                return response()->json([
                    'message' => 'Пользователь не найден в кабинете',
                    'error_code' => 'USER_NOT_IN_CABINET'
                ], 404);
            }

            // Get permissions with caching
            $permissions = $this->getUserPermissions($user->id, $cabinet->id);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'role' => $cabinetUser->role,
                    'is_owner' => $cabinetUser->is_owner,
                ],
                'permissions' => PermissionResource::collection($permissions)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user permissions', [
                'cabinet_id' => $cabinet->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка получения прав пользователя',
                'error_code' => 'PERMISSIONS_FETCH_FAILED'
            ], 500);
        }
    }

    /**
     * Assign permissions to user in cabinet.
     */
    public function store(AssignPermissionRequest $request, Cabinet $cabinet, User $user): JsonResponse
    {
        try {
            $currentUser = $request->user();

            // Check if current user has permission to assign permissions
            if (!$currentUser->hasPermissionInCabinet('user.manage', $cabinet->id)) {
                return response()->json([
                    'message' => 'Недостаточно прав для назначения прав пользователю',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS'
                ], 403);
            }

            // Get cabinet user relationship
            $cabinetUser = CabinetUser::where('cabinet_id', $cabinet->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$cabinetUser) {
                return response()->json([
                    'message' => 'Пользователь не найден в кабинете',
                    'error_code' => 'USER_NOT_IN_CABINET'
                ], 404);
            }

            // Get permission IDs
            $permissionIds = $request->validated()['permission_ids'];
            $permissions = Permission::whereIn('id', $permissionIds)->get();

            // Assign permissions
            $cabinetUser->permissions()->sync($permissionIds);

            // Clear permission cache
            $this->clearUserPermissionCache($user->id, $cabinet->id);

            // Fire events for audit logging
            foreach ($permissions as $permission) {
                PermissionAssigned::dispatch(
                    $currentUser,
                    $user,
                    $cabinet,
                    $permission,
                    $request->ip(),
                    $request->userAgent()
                );
            }

            return response()->json([
                'message' => 'Права успешно назначены',
                'permissions' => PermissionResource::collection($permissions)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to assign permissions', [
                'cabinet_id' => $cabinet->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка назначения прав',
                'error_code' => 'PERMISSIONS_ASSIGN_FAILED'
            ], 500);
        }
    }

    /**
     * Remove permissions from user in cabinet.
     */
    public function destroy(Request $request, Cabinet $cabinet, User $user): JsonResponse
    {
        try {
            $currentUser = $request->user();

            // Check if current user has permission to remove permissions
            if (!$currentUser->hasPermissionInCabinet('user.manage', $cabinet->id)) {
                return response()->json([
                    'message' => 'Недостаточно прав для удаления прав пользователя',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS'
                ], 403);
            }

            // Get cabinet user relationship
            $cabinetUser = CabinetUser::where('cabinet_id', $cabinet->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$cabinetUser) {
                return response()->json([
                    'message' => 'Пользователь не найден в кабинете',
                    'error_code' => 'USER_NOT_IN_CABINET'
                ], 404);
            }

            // Get permission IDs to remove
            $permissionIds = $request->input('permission_ids', []);
            
            if (empty($permissionIds)) {
                return response()->json([
                    'message' => 'Не указаны права для удаления',
                    'error_code' => 'PERMISSION_IDS_REQUIRED'
                ], 400);
            }

            // Get permissions before removal for audit
            $permissions = Permission::whereIn('id', $permissionIds)->get();

            // Remove permissions
            $cabinetUser->permissions()->detach($permissionIds);

            // Clear permission cache
            $this->clearUserPermissionCache($user->id, $cabinet->id);

            // Fire events for audit logging
            foreach ($permissions as $permission) {
                PermissionRevoked::dispatch(
                    $currentUser,
                    $user,
                    $cabinet,
                    $permission,
                    $request->ip(),
                    $request->userAgent()
                );
            }

            return response()->json([
                'message' => 'Права успешно удалены'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to remove permissions', [
                'cabinet_id' => $cabinet->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка удаления прав',
                'error_code' => 'PERMISSIONS_REMOVE_FAILED'
            ], 500);
        }
    }

    /**
     * Get user permissions with caching.
     */
    private function getUserPermissions(int $userId, int $cabinetId): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "user_permissions:{$userId}:{$cabinetId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $cabinetId) {
            $cabinetUser = CabinetUser::where('cabinet_id', $cabinetId)
                ->where('user_id', $userId)
                ->first();

            if (!$cabinetUser) {
                return collect();
            }

            return $cabinetUser->permissions()->get();
        });
    }

    /**
     * Clear user permission cache.
     */
    private function clearUserPermissionCache(int $userId, int $cabinetId): void
    {
        $cacheKey = "user_permissions:{$userId}:{$cabinetId}";
        Cache::forget($cacheKey);
    }
}
