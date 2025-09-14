<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'chat_id',
        'user_id',
        'text',
        'message_date',
        'processed'
    ];

    protected $casts = [
        'message_date' => 'datetime',
        'processed' => 'boolean'
    ];

    /**
     * Scope for unprocessed messages
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope for messages from specific user
     */
    public function scopeFromUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for messages after specific date
     */
    public function scopeAfter($query, $date)
    {
        return $query->where('message_date', '>', $date);
    }
}