<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Models\Concerns\BelongsToCurrentGym;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    use BelongsToCurrentGym;

    /** @var list<string> */
    protected $fillable = [
        'gym_id',
        'member_id',
        'plan_id',
        'start_date',
        'end_date',
        'sessions_remaining',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'sessions_remaining' => 'integer',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast() || $this->status === SubscriptionStatus::Expired;
    }

    public function hasSessionsLeft(): bool
    {
        if ($this->sessions_remaining === null) {
            return true;
        }

        return $this->sessions_remaining > 0;
    }

    public function isAccessible(): bool
    {
        return $this->status === SubscriptionStatus::Active
            && ! $this->end_date->isPast()
            && $this->hasSessionsLeft();
    }

    /**
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    public function scopeActive($query): Builder
    {
        return $query
            ->where('status', SubscriptionStatus::Active->value)
            ->where('end_date', '>=', now()->toDateString());
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
