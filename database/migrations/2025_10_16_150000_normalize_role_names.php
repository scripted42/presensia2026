<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::table('roles')->where('name', 'Admin')->update(['name' => 'admin']);
            DB::table('roles')->where('name', 'Guru')->update(['name' => 'teacher']);
            DB::table('roles')->where('name', 'Tata Usaha')->update(['name' => 'tu']);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        try {
            DB::table('roles')->where('name', 'admin')->update(['name' => 'Admin']);
            DB::table('roles')->where('name', 'teacher')->update(['name' => 'Guru']);
            DB::table('roles')->where('name', 'tu')->update(['name' => 'Tata Usaha']);
        } catch (\Throwable $e) {
            // ignore
        }
    }
};


