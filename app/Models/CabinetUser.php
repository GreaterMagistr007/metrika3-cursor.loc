<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\AuditableTrait;
use App\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class CabinetUser extends Model
{
    use HasFactory, HasPermissionsTrait, AuditableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cabinet_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cabinet_id',
        'user_id',
        'role',
        'is_owner',
        'joined_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_owner' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Get the cabinet that owns the cabinet user.
     */
    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    /**
     * Get the user that owns the cabinet user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the permissions for the cabinet user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'cabinet_user_permission');
    }

    /**
     * Check if cabinet user has specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()
            ->where('name', $permission)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Assign permission to cabinet user.
     */
    public function assignPermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    /**
     * Remove permission from cabinet user.
     */
    public function removePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    /**
     * Check if cabinet user is owner.
     */
    public function isOwner(): bool
    {
        return $this->is_owner;
    }
}
