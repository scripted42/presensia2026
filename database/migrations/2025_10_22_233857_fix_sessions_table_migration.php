<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mark the sessions migration as completed since the table already exists
        DB::table('migrations')->insert([
            'migration' => '2025_10_21_231449_create_sessions_table',
            'batch' => 19
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the sessions migration record
        DB::table('migrations')->where('migration', '2025_10_21_231449_create_sessions_table')->delete();
    }
};
