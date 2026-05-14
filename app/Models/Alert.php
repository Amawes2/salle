<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentGym;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use BelongsToCurrentGym;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gym_id',
        'user_id',
        'type',
        'title',
        'content',
        'is_read',
        'data',
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
            'data' => 'array',
        ];
    }

    /**
     * Alert types
     */
    public const TYPE_SUBSCRIPTION_EXPIRY = 'subscription_expiry';

    public const TYPE_LOW_SESSIONS = 'low_sessions';

    public const TYPE_PAYMENT_DUE = 'payment_due';

    public const TYPE_SYSTEM_NOTIFICATION = 'system_notification';

    public const TYPE_NEW_MESSAGE = 'new_message';

    /**
     * Get the gym that the alert belongs to.
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user that the alert belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the alert as read.
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark the alert as unread.
     */
    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    /**
     * Scope a query to only include unread alerts.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Get the icon for the alert type.
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_SUBSCRIPTION_EXPIRY => 'heroicon-o-exclamation-triangle',
            self::TYPE_LOW_SESSIONS => 'heroicon-o-clock',
            self::TYPE_PAYMENT_DUE => 'heroicon-o-banknotes',
            self::TYPE_SYSTEM_NOTIFICATION => 'heroicon-o-bell',
            self::TYPE_NEW_MESSAGE => 'heroicon-o-chat-bubble-left-right',
            default => 'heroicon-o-bell',
        };
    }

    /**
     * Get the color for the alert type.
     */
    public function getColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_SUBSCRIPTION_EXPIRY => 'danger',
            self::TYPE_LOW_SESSIONS => 'warning',
            self::TYPE_PAYMENT_DUE => 'warning',
            self::TYPE_SYSTEM_NOTIFICATION => 'info',
            self::TYPE_NEW_MESSAGE => 'primary',
            default => 'primary',
        };
    }
}
