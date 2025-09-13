<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'title',
        'text',
        'url',
        'button_text',
        'button_url',
        'is_active',
        'trigger_condition',
        'expires_at',
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
            'trigger_condition' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the message recipients.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class);
    }

    /**
     * Get the users through user_messages pivot table.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_messages')
            ->withPivot(['is_read', 'read_at'])
            ->withTimestamps();
    }

    /**
     * Get the user messages.
     */
    public function userMessages(): HasMany
    {
        return $this->hasMany(UserMessage::class);
    }

    /**
     * Scope to get active messages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get messages by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get non-expired messages.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if message is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if message is system type (broadcast).
     */
    public function isSystem(): bool
    {
        return $this->type === 'system';
    }

    /**
     * Check if message is persistent type.
     */
    public function isPersistent(): bool
    {
        return in_array($this->type, ['info', 'warning', 'error']);
    }

    /**
     * Check if message is toast type.
     */
    public function isToast(): bool
    {
        return in_array($this->type, ['success', 'error', 'warning', 'info']);
    }

    /**
     * Get available message types.
     */
    public static function getTypes(): array
    {
        return ['success', 'error', 'warning', 'info', 'system'];
    }
}
