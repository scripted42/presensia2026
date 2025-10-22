<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Identitas
            $table->string('nuptk')->nullable();
            $table->string('nip')->nullable();
            $table->string('nik')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('religion')->nullable();
            // Alamat
            $table->string('address_line')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('village')->nullable(); // kelurahan/desa
            $table->string('district')->nullable(); // kecamatan
            $table->string('city')->nullable(); // kabupaten/kota
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            // Pendidikan
            $table->string('last_education')->nullable();
            $table->string('major')->nullable();
            $table->string('university')->nullable();
            $table->string('graduation_year')->nullable();
            // Kepegawaian
            $table->string('ptk_type')->nullable(); // jenis PTK
            $table->string('employment_status')->nullable();
            $table->string('rank')->nullable(); // pangkat/golongan
            $table->string('salary_source')->nullable();
            $table->string('sk_cpns')->nullable();
            $table->date('tmt_cpns')->nullable();
            $table->timestamps();
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};














