<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Cabinet extends Model
{
    use HasFactory, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'owner_id',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the owner of the cabinet.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the users that belong to the cabinet.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cabinet_user')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get the cabinet user records.
     */
    public function cabinetUsers(): HasMany
    {
        return $this->hasMany(CabinetUser::class);
    }

    /**
     * Get the audit logs for the cabinet.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the message recipients for the cabinet.
     */
    public function messageRecipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class, 'recipient_id')
            ->where('recipient_type', 'cabinet');
    }

    /**
     * Check if user is member of this cabinet.
     */
    public function hasUser(int $userId): bool
    {
        return $this->users()->where('user_id', $userId)->exists();
    }

    /**
     * Check if user is owner of this cabinet.
     */
    public function isOwnedBy(int $userId): bool
    {
        return $this->owner_id === $userId;
    }

    /**
     * Get user role in this cabinet.
     */
    public function getUserRole(int $userId): ?string
    {
        $user = $this->users()->where('user_id', $userId)->first();
        return $user?->pivot?->role;
    }
}
