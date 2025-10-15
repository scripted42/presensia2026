<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop global unique index on email if exists, then add composite unique per tenant
        Schema::table('users', function (Blueprint $table) {
            // MySQL: drop index by name; ignore errors if not exists using try/catch
            try {
                DB::statement('ALTER TABLE `users` DROP INDEX `users_email_unique`');
            } catch (\Throwable $e) {
                // index may not exist; ignore
            }

            // Ensure composite unique per tenant
            try { DB::statement('ALTER TABLE `users` ADD UNIQUE `users_school_email_unique` (`school_id`, `email`)'); } catch (\Throwable $e) { }

            // Note: We intentionally do NOT enforce uniqueness on NIK/NIS/NISN here
            // to avoid migration failures on existing data. Validation will handle it.
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert composite uniques
            try { DB::statement('ALTER TABLE `users` DROP INDEX `users_school_email_unique`'); } catch (\Throwable $e) { }
            // no extra unique indexes to drop for nik/nis/nisn

            // Restore global email unique if desired
            try { DB::statement('ALTER TABLE `users` ADD UNIQUE `users_email_unique` (`email`)'); } catch (\Throwable $e) { }
        });
    }
};


