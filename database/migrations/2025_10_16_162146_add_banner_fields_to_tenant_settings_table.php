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
            $table->string('banner_image')->nullable()->after('app_favicon');
            $table->string('banner_text')->nullable()->after('banner_image');
            $table->string('school_photo')->nullable()->after('banner_text');
            $table->integer('school_photo_opacity')->default(10)->after('school_photo');
            $table->text('topbar_announcement')->nullable()->after('school_photo_opacity');
            $table->boolean('show_announcement')->default(false)->after('topbar_announcement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'banner_image',
                'banner_text', 
                'school_photo',
                'school_photo_opacity',
                'topbar_announcement',
                'show_announcement'
            ]);
        });
    }
};