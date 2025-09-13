<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Permission;

trait HasPermissionsTrait
{
    /**
     * Check if the model has a specific permission.
     */
    public function hasPermissionTo(string $permission): bool
    {
        if (method_exists($this, 'permissions')) {
            return $this->permissions()
                ->where('name', $permission)
                ->where('is_active', true)
                ->exists();
        }

        return false;
    }

    /**
     * Assign a permission to the model.
     */
    public function assignPermission(Permission $permission): void
    {
        if (method_exists($this, 'permissions')) {
            $this->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }

    /**
     * Remove a permission from the model.
     */
    public function removePermission(Permission $permission): void
    {
        if (method_exists($this, 'permissions')) {
            $this->permissions()->detach($permission->id);
        }
    }

    /**
     * Sync permissions for the model.
     */
    public function syncPermissions(array $permissionIds): void
    {
        if (method_exists($this, 'permissions')) {
            $this->permissions()->sync($permissionIds);
        }
    }

    /**
     * Get all permission names for the model.
     */
    public function getPermissionNames(): array
    {
        if (method_exists($this, 'permissions')) {
            return $this->permissions()
                ->where('is_active', true)
                ->pluck('name')
                ->toArray();
        }

        return [];
    }

    /**
     * Check if the model has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (method_exists($this, 'permissions')) {
            return $this->permissions()
                ->whereIn('name', $permissions)
                ->where('is_active', true)
                ->exists();
        }

        return false;
    }

    /**
     * Check if the model has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (method_exists($this, 'permissions')) {
            $userPermissions = $this->getPermissionNames();
            return count(array_intersect($permissions, $userPermissions)) === count($permissions);
        }

        return false;
    }
}
