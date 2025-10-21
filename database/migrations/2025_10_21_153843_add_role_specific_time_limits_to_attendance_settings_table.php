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
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->time('teacher_max_time')->default('06:30:00')->after('check_out_time');
            $table->time('student_max_time')->default('06:30:00')->after('teacher_max_time');
            $table->time('other_roles_max_time')->default('07:00:00')->after('student_max_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['teacher_max_time', 'student_max_time', 'other_roles_max_time']);
        });
    }
};
