<?php

namespace App\Models\Concerns;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToCurrentGym
{
    protected static function bootBelongsToCurrentGym(): void
    {
        static::creating(function ($model): void {
            if (! empty($model->gym_id)) {
                return;
            }

            $gymId = static::resolveCurrentGymId();

            if ($gymId === -1) {
                throw new \InvalidArgumentException('L\'utilisateur n\'a pas de salle de sport assignée.');
            }

            if ($gymId !== null) {
                $model->gym_id = $gymId;
            }
        });

        static::addGlobalScope('current_gym', function (Builder $query): void {
            $gymId = static::resolveCurrentGymId();

            if ($gymId === null) {
                return;
            }

            if ($gymId !== -1) {
                $query->where($query->getModel()->getTable().'.gym_id', $gymId);
            }
        });
    }

    protected static function resolveCurrentGymId(): ?int
    {
        $panelId = Filament::getCurrentPanel()?->getId();
        $tenantId = Filament::getTenant()?->getKey();

        if ($tenantId) {
            return (int) $tenantId;
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        // Super-admin panel should keep full visibility.
        if ($panelId === 'super-admin') {
            return null;
        }

        $gymId = $user->gyms()->value('id');

        if (! $gymId) {
            $gymId = $user->managedGyms()->value('gyms.id');
        }

        if (! $gymId) {
            // User without gym must not see cross-tenant data.
            return -1;
        }

        return (int) $gymId;
    }
}

