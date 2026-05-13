<?php

namespace App\Http\Controllers;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class GymRegistrationController extends Controller
{
    public function create(): View
    {
        return view('gym.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gym_name' => ['required', 'string', 'max:190'],
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $slugBase = Str::slug($data['gym_name']);
        $slug = $slugBase;
        $counter = 1;

        while (Gym::query()->where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.$counter;
            $counter++;
        }

        Gym::query()->create([
            'name' => $data['gym_name'],
            'slug' => $slug,
            'owner_id' => $user->id,
            'plan_saas' => 'trial',
            'is_active' => true,
            'expires_at' => now()->addMonth(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->to('/admin')
            ->with('status', 'Votre espace salle a ete cree. Vous pouvez commencer a la gerer maintenant.');
    }
}
