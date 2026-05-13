<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create the pivot table only if it does not already exist
        if (! Schema::hasTable('gym_user')) {
            Schema::create('gym_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gym_id')->constrained('gyms')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['gym_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gym_user')) {
            Schema::dropIfExists('gym_user');
        }
    }
};
