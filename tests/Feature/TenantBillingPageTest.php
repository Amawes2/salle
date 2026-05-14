<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantBillingPageTest extends TestCase
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
    }

    public function test_owner_can_open_billing_page_without_filament_tenant_context(): void
    {
        $user = User::query()->create([
            'name' => 'Barry Mamadou',
            'email' => 'bary@example.com',
            'password' => Hash::make('password'),
        ]);

        Gym::query()->create([
            'name' => 'Baryy Salle',
            'slug' => 'baryy-salle',
            'owner_id' => $user->id,
            'plan_saas' => 'basic',
            'is_active' => true,
            'expires_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user)->get(route('filament.admin.pages.tenant-billing'));

        $response->assertOk();
        $response->assertSeeText('Baryy Salle');
    }

    public function test_page_renders_generic_message_when_user_has_no_gym(): void
    {
        $user = User::query()->create([
            'name' => 'No Gym User',
            'email' => 'nogym@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->get(route('filament.admin.pages.tenant-billing'));

        $response->assertOk();
        $response->assertSeeText('Impossible de déterminer la salle concernée');
    }
}
