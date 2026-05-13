<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthRecoveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_super_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('gyms', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan_saas')->default('basic');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->float('walk_in_price')->default(0);
            $table->timestamps();
        });

        Schema::create('gym_user', function (Blueprint $table): void {
            $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['gym_id', 'user_id']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_gym_registration_redirects_directly_to_admin_panel(): void
    {
        $response = $this->post(route('gyms.register.store'), [
            'gym_name' => 'Baryy Salle',
            'name' => 'Barry Mamadou',
            'email' => 'barry@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::query()->where('email', 'barry@example.com')->firstOrFail();

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('gyms', [
            'owner_id' => $user->id,
            'name' => 'Baryy Salle',
        ]);
        $this->assertNotSame('Password123!', $user->password);
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    public function test_registration_rejects_a_weak_password(): void
    {
        $response = $this->from(route('gyms.register'))->post(route('gyms.register.store'), [
            'gym_name' => 'Baryy Salle',
            'name' => 'Barry Mamadou',
            'email' => 'barry@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('gyms.register'));
        $response->assertSessionHasErrors('password');
    }

    public function test_admin_panel_exposes_recovery_routes(): void
    {
        $routes = app('router')->getRoutes();

        $this->assertNull($routes->getByName('verification.challenge'));
        $this->assertNotNull($routes->getByName('filament.admin.auth.password-reset.request'));
        $this->assertNotNull($routes->getByName('filament.admin.auth.password-reset.reset'));
        $this->assertNotNull($routes->getByName('filament.admin.auth.profile'));
    }
}
