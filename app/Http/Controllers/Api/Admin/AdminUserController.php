<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\AdminUserCreateRequest;
use App\Http\Requests\Api\Admin\AdminUserUpdateRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\AdminUser;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminUserController extends Controller
{
    public function __construct(
        private readonly AdminAuthService $adminAuthService
    ) {}

    /**
     * Display a listing of admin users.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $role = $request->get('role');

        $query = AdminUser::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        $admins = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'admin_users' => AdminUserResource::collection($admins->items()),
            'pagination' => [
                'current_page' => $admins->currentPage(),
                'last_page' => $admins->lastPage(),
                'per_page' => $admins->perPage(),
                'total' => $admins->total(),
                'from' => $admins->firstItem(),
                'to' => $admins->lastItem(),
            ],
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    /**
     * Store a newly created admin user.
     */
    public function store(AdminUserCreateRequest $request): JsonResponse
    {
        $admin = $this->adminAuthService->register($request->validated());

        return response()->json([
            'message' => 'Администратор успешно создан',
            'admin_user' => new AdminUserResource($admin),
        ], 201);
    }

    /**
     * Display the specified admin user.
     */
    public function show(AdminUser $adminUser): JsonResponse
    {
        return response()->json([
            'admin_user' => new AdminUserResource($adminUser),
        ]);
    }

    /**
     * Update the specified admin user.
     */
    public function update(AdminUserUpdateRequest $request, AdminUser $adminUser): JsonResponse
    {
        $admin = $this->adminAuthService->updateProfile($adminUser, $request->validated());

        return response()->json([
            'message' => 'Администратор успешно обновлен',
            'admin_user' => new AdminUserResource($admin),
        ]);
    }

    /**
     * Remove the specified admin user.
     */
    public function destroy(AdminUser $adminUser): JsonResponse
    {
        // Prevent deletion of super admin
        if ($adminUser->isSuperAdmin()) {
            return response()->json([
                'message' => 'Невозможно удалить супер-администратора',
                'error_code' => 'CANNOT_DELETE_SUPER_ADMIN',
            ], 422);
        }

        $this->adminAuthService->deleteAdmin($adminUser);

        return response()->json([
            'message' => 'Администратор успешно удален',
        ]);
    }

    /**
     * Update admin user role.
     */
    public function updateRole(Request $request, AdminUser $adminUser): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,super_admin',
        ]);

        // Prevent changing super admin role
        if ($adminUser->isSuperAdmin()) {
            return response()->json([
                'message' => 'Невозможно изменить роль супер-администратора',
                'error_code' => 'CANNOT_CHANGE_SUPER_ADMIN_ROLE',
            ], 422);
        }

        $admin = $this->adminAuthService->updateAdminRole($adminUser, $validated['role']);

        return response()->json([
            'message' => 'Роль администратора успешно обновлена',
            'admin_user' => new AdminUserResource($admin),
        ]);
    }
}
