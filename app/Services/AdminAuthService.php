<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final class AdminAuthService
{
    /**
     * Register a new admin user.
     */
    public function register(array $data): AdminUser
    {
        $admin = AdminUser::create([
            'phone' => $data['phone'],
            'name' => $data['name'],
            'role' => $data['role'] ?? 'admin',
            'phone_verified_at' => now(),
        ]);

        Log::info('Admin user registered', [
            'admin_id' => $admin->id,
            'phone' => $admin->phone,
            'role' => $admin->role,
        ]);

        return $admin;
    }

    /**
     * Login admin user by phone.
     */
    public function login(string $phone): array
    {
        $admin = AdminUser::where('phone', $phone)->first();

        if (!$admin) {
            throw new \Exception('Администратор не найден');
        }

        // Update last login
        $admin->update(['last_login_at' => now()]);

        // Create token
        $token = $admin->createToken('admin-token')->plainTextToken;

        Log::info('Admin user logged in', [
            'admin_id' => $admin->id,
            'phone' => $admin->phone,
            'role' => $admin->role,
        ]);

        return [
            'admin' => $admin,
            'token' => $token,
        ];
    }

    /**
     * Logout admin user.
     */
    public function logout(AdminUser $admin): void
    {
        $admin->currentAccessToken()->delete();

        Log::info('Admin user logged out', [
            'admin_id' => $admin->id,
            'phone' => $admin->phone,
        ]);
    }

    /**
     * Get admin profile.
     */
    public function getProfile(AdminUser $admin): AdminUser
    {
        return $admin->load('tokens');
    }

    /**
     * Update admin profile.
     */
    public function updateProfile(AdminUser $admin, array $data): AdminUser
    {
        $admin->update($data);

        Log::info('Admin profile updated', [
            'admin_id' => $admin->id,
            'updated_fields' => array_keys($data),
        ]);

        return $admin->fresh();
    }

    /**
     * Check if admin has super admin role.
     */
    public function isSuperAdmin(AdminUser $admin): bool
    {
        return $admin->isSuperAdmin();
    }

    /**
     * Check if admin has admin role.
     */
    public function isAdmin(AdminUser $admin): bool
    {
        return $admin->isAdmin();
    }

    /**
     * Get all admins with pagination.
     */
    public function getAdmins(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return AdminUser::orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get admin by ID.
     */
    public function getAdminById(int $id): ?AdminUser
    {
        return AdminUser::find($id);
    }

    /**
     * Update admin role.
     */
    public function updateAdminRole(AdminUser $admin, string $role): AdminUser
    {
        $admin->update(['role' => $role]);

        Log::info('Admin role updated', [
            'admin_id' => $admin->id,
            'new_role' => $role,
        ]);

        return $admin->fresh();
    }

    /**
     * Delete admin user.
     */
    public function deleteAdmin(AdminUser $admin): bool
    {
        // Revoke all tokens
        $admin->tokens()->delete();

        $deleted = $admin->delete();

        Log::info('Admin user deleted', [
            'admin_id' => $admin->id,
            'phone' => $admin->phone,
        ]);

        return $deleted;
    }
}
