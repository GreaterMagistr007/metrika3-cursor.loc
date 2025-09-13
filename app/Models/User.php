<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone',
        'telegram_id',
        'telegram_data',
        'name',
        'phone_verified_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'telegram_data' => 'array',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the cabinets that the user belongs to.
     */
    public function cabinets(): BelongsToMany
    {
        return $this->belongsToMany(Cabinet::class, 'cabinet_user')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get the cabinets owned by the user.
     */
    public function ownedCabinets(): HasMany
    {
        return $this->hasMany(Cabinet::class, 'owner_id');
    }

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the user messages.
     */
    public function userMessages(): HasMany
    {
        return $this->hasMany(UserMessage::class);
    }

    /**
     * Get the messages through user_messages pivot table.
     */
    public function messages(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'user_messages')
            ->withPivot(['is_read', 'read_at'])
            ->withTimestamps();
    }

    /**
     * Check if user has permission in specific cabinet.
     */
    public function hasPermissionInCabinet(string $permission, int $cabinetId): bool
    {
        $cabinetUser = CabinetUser::where('user_id', $this->id)
            ->where('cabinet_id', $cabinetId)
            ->first();

        if (!$cabinetUser) {
            return false;
        }

        return $cabinetUser->hasPermission($permission);
    }

    /**
     * Check if user is owner of cabinet.
     */
    public function isOwnerOf(int $cabinetId): bool
    {
        return $this->ownedCabinets()->where('id', $cabinetId)->exists();
    }
}
