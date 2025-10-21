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
        Schema::create('daily_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->date('date'); // Specific date for override
            $table->time('max_check_in_time'); // Override time for this specific date
            $table->json('affected_roles')->nullable(); // ['teacher', 'student'] or null for all
            $table->string('reason')->nullable(); // e.g., "Upacara Hari Kemerdekaan", "Rapat Khusus"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->unique(['school_id', 'date']);
            $table->index(['school_id', 'date', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_overrides');
    }
};
