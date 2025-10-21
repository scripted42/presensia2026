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
        Schema::create('special_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name'); // e.g., "Upacara Senin", "Rapat Guru"
            $table->text('description')->nullable();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('max_check_in_time'); // e.g., 07:30 for upacara
            $table->json('affected_roles')->nullable(); // ['teacher', 'student'] or null for all
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable(); // Optional: when this schedule starts
            $table->date('end_date')->nullable(); // Optional: when this schedule ends
            $table->timestamps();
            
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->index(['school_id', 'day_of_week', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_schedules');
    }
};
