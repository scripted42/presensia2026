<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Identitas
            $table->string('nis')->nullable();
            $table->string('nisn')->nullable();
            $table->string('nik')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('religion')->nullable();
            $table->string('gender')->nullable();
            // Alamat
            $table->string('address_line')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            // Orang tua/wali ringkas
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('father_job')->nullable();
            $table->string('mother_job')->nullable();
            $table->string('guardian_job')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('guardian_phone')->nullable();
            // Akademik
            $table->string('admission_year')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('transportation')->nullable();
            $table->timestamps();
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};





