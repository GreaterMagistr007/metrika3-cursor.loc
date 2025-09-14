<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\AdminLoginRequest;
use App\Http\Requests\Api\Admin\AdminRegisterRequest;
use App\Http\Requests\Api\Admin\AdminUpdateProfileRequest;
use App\Http\Resources\AdminUserResource;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AdminAuthService $adminAuthService
    ) {}

    /**
     * Register a new admin user.
     */
    public function register(AdminRegisterRequest $request): JsonResponse
    {
        $admin = $this->adminAuthService->register($request->validated());

        return response()->json([
            'message' => 'Администратор успешно зарегистрирован',
            'admin' => new AdminUserResource($admin),
        ], 201);
    }

    /**
     * Login admin user.
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $result = $this->adminAuthService->login($request->validated('phone'));

        return response()->json([
            'message' => 'Успешная авторизация',
            'admin' => new AdminUserResource($result['admin']),
            'token' => $result['token'],
        ]);
    }

    /**
     * Logout admin user.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->adminAuthService->logout($request->user());

        return response()->json([
            'message' => 'Успешный выход из системы',
        ]);
    }

    /**
     * Get admin profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $admin = $this->adminAuthService->getProfile($request->user());

        return response()->json([
            'admin' => new AdminUserResource($admin),
        ]);
    }

    /**
     * Update admin profile.
     */
    public function updateProfile(AdminUpdateProfileRequest $request): JsonResponse
    {
        $admin = $this->adminAuthService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Профиль успешно обновлен',
            'admin' => new AdminUserResource($admin),
        ]);
    }
}
