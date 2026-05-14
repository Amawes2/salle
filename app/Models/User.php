<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password', 'is_super_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function gyms(): HasMany
    {
        return $this->hasMany(Gym::class, 'owner_id');
    }

    public function managedGyms(): BelongsToMany
    {
        return $this->belongsToMany(Gym::class, 'gym_user');
    }

    /**
     * Get the conversations where the user is a super admin.
     */
    public function superAdminConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'super_admin_id');
    }

    /**
     * Get the conversations for the gyms owned by the user.
     */
    public function gymConversations(): HasManyThrough
    {
        return $this->hasManyThrough(Conversation::class, Gym::class, 'owner_id');
    }

    /**
     * Get all messages sent by the user.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get all alerts for the user.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function getTenants(Panel $panel): array|Collection
    {
        // Return both owned gyms and gyms the user has been assigned to manage
        return $this->gyms->merge($this->managedGyms);
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->gyms()->whereKey($tenant)->exists()
            || $this->managedGyms()->whereKey($tenant)->exists();
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->getTenants($panel)->first() ?: null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Restrictions according to panel identity
        if ($panel->getId() === 'super-admin') {
            // Primary check: explicit flag in database
            if (! empty($this->is_super_admin)) {
                return true;
            }

            // Fallback 1: allow the first user (id=1) to access while migrations/runups are applied
            if ($this->getKey() === 1) {
                return true;
            }

            // Fallback 2: allow email configured via env var SUPER_ADMIN_EMAIL or APP_SUPER_ADMIN_EMAIL
            $superEmail = env('SUPER_ADMIN_EMAIL') ?: env('APP_SUPER_ADMIN_EMAIL');
            if ($superEmail && isset($this->email) && $this->email === $superEmail) {
                return true;
            }

            return false;
        }

        // For default tenant panel
        return true;
    }
}
