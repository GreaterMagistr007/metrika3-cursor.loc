<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class MessageRecipient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message_id',
        'recipient_type',
        'recipient_id',
    ];

    /**
     * Get the message that owns the recipient.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the recipient (polymorphic).
     */
    public function recipient(): MorphTo
    {
        return $this->morphTo('recipient', 'recipient_type', 'recipient_id');
    }

    /**
     * Scope to filter by recipient type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('recipient_type', $type);
    }

    /**
     * Scope to filter by recipient ID.
     */
    public function scopeByRecipient($query, int $recipientId)
    {
        return $query->where('recipient_id', $recipientId);
    }

    /**
     * Check if recipient is a user.
     */
    public function isUser(): bool
    {
        return $this->recipient_type === 'user';
    }

    /**
     * Check if recipient is a cabinet.
     */
    public function isCabinet(): bool
    {
        return $this->recipient_type === 'cabinet';
    }

    /**
     * Check if recipient is all users.
     */
    public function isAll(): bool
    {
        return $this->recipient_type === 'all';
    }
}
