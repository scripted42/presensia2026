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
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->string('school_photo_position_x')->default('center')->after('school_photo_opacity');
            $table->string('school_photo_position_y')->default('center')->after('school_photo_position_x');
            $table->integer('school_photo_scale')->default(100)->after('school_photo_position_y');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'school_photo_position_x',
                'school_photo_position_y',
                'school_photo_scale'
            ]);
        });
    }
};