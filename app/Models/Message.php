<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'is_read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user that sent the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Determine if the message is from the current user.
     */
    public function isFromCurrentUser(): bool
    {
        return $this->user_id === auth()->id();
    }

    /**
     * Get the formatted created_at attribute.
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        $date = $this->created_at;

        if ($date->isToday()) {
            return $date->format('H:i');
        }

        if ($date->isYesterday()) {
            return 'Hier '.$date->format('H:i');
        }

        if ($date->isCurrentYear()) {
            return $date->format('d M H:i');
        }

        return $date->format('d/m/Y H:i');
    }
}
