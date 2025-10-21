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
        // Add indexes for attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['date', 'user_id'], 'idx_attendances_date_user');
            $table->index(['status', 'date'], 'idx_attendances_status_date');
            $table->index(['user_id', 'date'], 'idx_attendances_user_date');
            $table->index(['date'], 'idx_attendances_date');
        });

        // Add indexes for holiday_schedules table
        Schema::table('holiday_schedules', function (Blueprint $table) {
            $table->index(['date', 'school_id'], 'idx_holidays_date_school');
            $table->index(['school_id', 'is_active'], 'idx_holidays_school_active');
            $table->index(['date', 'is_active'], 'idx_holidays_date_active');
        });

        // Add indexes for special_schedules table
        Schema::table('special_schedules', function (Blueprint $table) {
            $table->index(['day_of_week', 'is_active'], 'idx_special_day_active');
            $table->index(['school_id', 'is_active'], 'idx_special_school_active');
            $table->index(['start_date', 'end_date'], 'idx_special_date_range');
        });

        // Add indexes for daily_overrides table
        Schema::table('daily_overrides', function (Blueprint $table) {
            $table->index(['date', 'school_id'], 'idx_overrides_date_school');
            $table->index(['school_id', 'is_active'], 'idx_overrides_school_active');
        });

        // Add indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['school_id', 'user_type'], 'idx_users_school_type');
            $table->index(['school_id'], 'idx_users_school');
            $table->index(['user_type'], 'idx_users_type');
        });

        // Add indexes for attendance_settings table
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->index(['school_id', 'is_active'], 'idx_settings_school_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes for attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_date_user');
            $table->dropIndex('idx_attendances_status_date');
            $table->dropIndex('idx_attendances_user_date');
            $table->dropIndex('idx_attendances_date');
        });

        // Remove indexes for holiday_schedules table
        Schema::table('holiday_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_holidays_date_school');
            $table->dropIndex('idx_holidays_school_active');
            $table->dropIndex('idx_holidays_date_active');
        });

        // Remove indexes for special_schedules table
        Schema::table('special_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_special_day_active');
            $table->dropIndex('idx_special_school_active');
            $table->dropIndex('idx_special_date_range');
        });

        // Remove indexes for daily_overrides table
        Schema::table('daily_overrides', function (Blueprint $table) {
            $table->dropIndex('idx_overrides_date_school');
            $table->dropIndex('idx_overrides_school_active');
        });

        // Remove indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_school_type');
            $table->dropIndex('idx_users_school');
            $table->dropIndex('idx_users_type');
        });

        // Remove indexes for attendance_settings table
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropIndex('idx_settings_school_active');
        });
    }
};