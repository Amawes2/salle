<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('gyms')) {
            Schema::create('gyms', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
                $table->string('plan_saas')->default('basic');
                $table->boolean('is_active')->default(true);
                $table->date('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank to avoid dropping gyms table twice
        // when both gyms migrations exist.
    }
};
