<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentGym;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
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
        'super_admin_id',
        'title',
    ];

    /**
     * Get the gym that owns the conversation.
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the super admin that participates in the conversation.
     */
    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    /**
     * Get the messages for the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest message for the conversation.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Get the unread messages count for a specific user.
     */
    public function unreadMessagesCount(User $user)
    {
        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark all messages as read for a specific user.
     */
    public function markAsRead(User $user)
    {
        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get the title for the conversation.
     * If no title is set, generate one based on the participants.
     */
    public function getDisplayTitleAttribute()
    {
        if ($this->title) {
            return $this->title;
        }

        if ($this->superAdmin) {
            return 'Conversation avec '.$this->superAdmin->name;
        }

        return 'Conversation avec l\'administrateur';
    }

    /**
     * Get the count of messages for the conversation.
     */
    public function getMessagesCountAttribute()
    {
        return $this->messages()->count();
    }
}
