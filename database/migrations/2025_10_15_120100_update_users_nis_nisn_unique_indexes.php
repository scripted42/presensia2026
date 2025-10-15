<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update NIS and NISN unique constraints to be per-tenant
        Schema::table('users', function (Blueprint $table) {
            // Drop existing global unique indexes if they exist
            try { DB::statement('ALTER TABLE `users` DROP INDEX `users_nis_unique`'); } catch (\Throwable $e) { }
            try { DB::statement('ALTER TABLE `users` DROP INDEX `users_nisn_unique`'); } catch (\Throwable $e) { }
            
            // Add composite unique per tenant
            try { DB::statement('ALTER TABLE `users` ADD UNIQUE `users_school_nis_unique` (`school_id`, `nis`)'); } catch (\Throwable $e) { }
            try { DB::statement('ALTER TABLE `users` ADD UNIQUE `users_school_nisn_unique` (`school_id`, `nisn`)'); } catch (\Throwable $e) { }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert composite uniques
            try { DB::statement('ALTER TABLE `users` DROP INDEX `users_school_nis_unique`'); } catch (\Throwable $e) { }
            try { DB::statement('ALTER TABLE `users` DROP INDEX `users_school_nisn_unique`'); } catch (\Throwable $e) { }
            
            // Restore global unique if desired
            try { DB::statement('ALTER TABLE `users` ADD UNIQUE `users_nis_unique` (`nis`)'); } catch (\Throwable $e) { }
            try { DB::statement('ALTER TABLE `users` ADD UNIQUE `users_nisn_unique` (`nisn`)'); } catch (\Throwable $e) { }
        });
    }
};

