<?php

namespace App\Support;

use App\Models\Gym;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class CurrentGymResolver
{
    public static function resolve(): ?Gym
    {
        $tenant = Filament::getTenant();

        if ($tenant instanceof Gym) {
            return $tenant;
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        return $user->gyms()->orderBy('id')->first()
            ?? $user->managedGyms()->orderBy('gyms.id')->first();
    }
}
