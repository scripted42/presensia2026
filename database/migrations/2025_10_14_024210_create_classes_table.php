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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "X IPA 1", "XII IPS 2"
            $table->string('level'); // e.g., "X", "XI", "XII"
            $table->string('major')->nullable(); // e.g., "IPA", "IPS", "Bahasa"
            $table->integer('year'); // Tahun ajaran
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
