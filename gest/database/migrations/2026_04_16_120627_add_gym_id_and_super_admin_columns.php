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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('password');
        });

        $tables = ['members', 'plans', 'subscriptions', 'payments', 'check_ins'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Pour supporter les données existantes avant le multi-tenant, 
                // on met d'abord la colonne en nullable() ou on exécute une migration propre,
                // mais la directive est de la créer avec un cascadeOnDelete.
                $table->foreignId('gym_id')->nullable()->constrained('gyms')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['members', 'plans', 'subscriptions', 'payments', 'check_ins'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['gym_id']);
                $table->dropColumn('gym_id');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};
