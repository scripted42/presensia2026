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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->time('check_in_time')->default('07:00:00');
            $table->time('check_out_time')->default('15:00:00');
            $table->decimal('location_latitude', 10, 8);
            $table->decimal('location_longitude', 11, 8);
            $table->string('location_name');
            $table->integer('radius_meters')->default(100); // Radius dalam meter
            $table->integer('qr_code_duration')->default(10); // Durasi QR code dalam detik
            $table->boolean('require_photo')->default(true);
            $table->boolean('require_location')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
