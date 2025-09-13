<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cabinet;
use App\Models\CabinetUser;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class CabinetService
{
    /**
     * Create a new cabinet.
     */
    public function createCabinet(User $owner, string $name, ?string $description = null): Cabinet
    {
        return DB::transaction(function () use ($owner, $name, $description) {
            // Create cabinet
            $cabinet = Cabinet::create([
                'name' => $name,
                'description' => $description,
                'owner_id' => $owner->id,
            ]);

            // Add owner as cabinet user with admin role
            $cabinetUser = CabinetUser::create([
                'cabinet_id' => $cabinet->id,
                'user_id' => $owner->id,
                'role' => 'admin',
                'is_owner' => true,
            ]);

            // Assign all permissions to owner
            $allPermissions = Permission::all();
            $cabinetUser->permissions()->attach($allPermissions->pluck('id'));

            // Log the action
            $owner->logAuditEvent('cabinet_created', 'Создан кабинет', [
                'cabinet_id' => $cabinet->id,
                'cabinet_name' => $cabinet->name,
            ]);

            return $cabinet;
        });
    }

    /**
     * Invite user to cabinet.
     */
    public function inviteUserToCabinet(Cabinet $cabinet, string $phone, string $role = 'operator'): array
    {
        try {
            // Find user by phone
            $user = User::where('phone', $phone)->first();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Пользователь с таким номером телефона не найден',
                    'error_code' => 'USER_NOT_FOUND'
                ];
            }

            // Check if user is already in cabinet
            if ($cabinet->users()->where('user_id', $user->id)->exists()) {
                return [
                    'success' => false,
                    'message' => 'Пользователь уже является участником кабинета',
                    'error_code' => 'USER_ALREADY_IN_CABINET'
                ];
            }

            return DB::transaction(function () use ($cabinet, $user, $role) {
                // Add user to cabinet
                $cabinetUser = CabinetUser::create([
                    'cabinet_id' => $cabinet->id,
                    'user_id' => $user->id,
                    'role' => $role,
                    'is_owner' => false,
                ]);

                // Assign default permissions based on role
                $this->assignDefaultPermissions($cabinetUser, $role);

                // Log the action
                $cabinet->owner->logAuditEvent('user_invited', 'Пользователь приглашен в кабинет', [
                    'cabinet_id' => $cabinet->id,
                    'invited_user_id' => $user->id,
                    'invited_user_phone' => $user->phone,
                    'role' => $role,
                ]);

                return [
                    'success' => true,
                    'message' => 'Пользователь успешно приглашен в кабинет',
                    'cabinet_user' => $cabinetUser
                ];
            });

        } catch (\Exception $e) {
            Log::error('Failed to invite user to cabinet', [
                'cabinet_id' => $cabinet->id,
                'phone' => $phone,
                'role' => $role,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка приглашения пользователя',
                'error_code' => 'INVITE_ERROR'
            ];
        }
    }

    /**
     * Remove user from cabinet.
     */
    public function removeUserFromCabinet(Cabinet $cabinet, User $user): void
    {
        DB::transaction(function () use ($cabinet, $user) {
            // Remove user from cabinet
            $cabinet->users()->where('user_id', $user->id)->delete();

            // Log the action
            $cabinet->owner->logAuditEvent('user_removed', 'Пользователь удален из кабинета', [
                'cabinet_id' => $cabinet->id,
                'removed_user_id' => $user->id,
                'removed_user_phone' => $user->phone,
            ]);
        });
    }

    /**
     * Transfer cabinet ownership.
     */
    public function transferOwnership(Cabinet $cabinet, string $newOwnerPhone): array
    {
        try {
            // Find new owner by phone
            $newOwner = User::where('phone', $newOwnerPhone)->first();
            
            if (!$newOwner) {
                return [
                    'success' => false,
                    'message' => 'Пользователь с таким номером телефона не найден',
                    'error_code' => 'USER_NOT_FOUND'
                ];
            }

            // Check if new owner is already in cabinet
            $cabinetUser = CabinetUser::where('cabinet_id', $cabinet->id)
                ->where('user_id', $newOwner->id)
                ->first();
            if (!$cabinetUser) {
                return [
                    'success' => false,
                    'message' => 'Пользователь не является участником кабинета',
                    'error_code' => 'USER_NOT_IN_CABINET'
                ];
            }

            return DB::transaction(function () use ($cabinet, $newOwner, $cabinetUser) {
                $oldOwner = $cabinet->owner;

                // Update cabinet owner
                $cabinet->update(['owner_id' => $newOwner->id]);

                // Update cabinet user records
                // Remove owner flag from old owner
                $oldOwnerCabinetUser = CabinetUser::where('cabinet_id', $cabinet->id)
                    ->where('user_id', $oldOwner->id)
                    ->first();
                if ($oldOwnerCabinetUser) {
                    $oldOwnerCabinetUser->update(['is_owner' => false]);
                }

                // Set owner flag for new owner
                $cabinetUser->update(['is_owner' => true, 'role' => 'admin']);

                // Assign all permissions to new owner
                $allPermissions = Permission::all();
                $cabinetUser->permissions()->sync($allPermissions->pluck('id'));

                // Log the action
                $oldOwner->logAuditEvent('ownership_transferred', 'Права владения кабинетом переданы', [
                    'cabinet_id' => $cabinet->id,
                    'new_owner_id' => $newOwner->id,
                    'new_owner_phone' => $newOwner->phone,
                ]);

                return [
                    'success' => true,
                    'message' => 'Права владения успешно переданы',
                    'cabinet_user' => $cabinetUser
                ];
            });

        } catch (\Exception $e) {
            Log::error('Failed to transfer cabinet ownership', [
                'cabinet_id' => $cabinet->id,
                'new_owner_phone' => $newOwnerPhone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка передачи прав владения',
                'error_code' => 'TRANSFER_ERROR'
            ];
        }
    }

    /**
     * Delete cabinet.
     */
    public function deleteCabinet(Cabinet $cabinet): void
    {
        DB::transaction(function () use ($cabinet) {
            $owner = $cabinet->owner;

            // Delete cabinet (cascade will handle related records)
            $cabinet->delete();

            // Log the action
            $owner->logAuditEvent('cabinet_deleted', 'Кабинет удален', [
                'cabinet_id' => $cabinet->id,
                'cabinet_name' => $cabinet->name,
            ]);
        });
    }

    /**
     * Assign default permissions based on role.
     */
    private function assignDefaultPermissions(CabinetUser $cabinetUser, string $role): void
    {
        $permissions = match ($role) {
            'admin' => Permission::all(),
            'manager' => Permission::whereIn('category', ['machines', 'reports', 'users'])->get(),
            'operator' => Permission::whereIn('category', ['machines'])->get(),
            default => Permission::whereIn('category', ['machines'])->get(),
        };

        $cabinetUser->permissions()->attach($permissions->pluck('id'));
    }
}
