<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('roles', 'display_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('display_name')->nullable()->after('name');
            });
        }

        // Backfill common roles with proper display names in Indonesian
        $map = [
            'admin' => 'Admin',
            'teacher' => 'Guru',
            'tu' => 'Tata Usaha',
            'bk' => 'BK',
            'headmaster' => 'Kepala Sekolah',
            'kesiswaan' => 'Kesiswaan',
            'student' => 'Siswa',
            'super-admin' => 'Super Admin',
        ];
        foreach ($map as $name => $label) {
            DB::table('roles')->where('name', $name)->update(['display_name' => $label]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'display_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('display_name');
            });
        }
    }
};


