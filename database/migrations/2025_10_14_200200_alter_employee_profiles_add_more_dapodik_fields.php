<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->string('npwp')->nullable()->after('phone');
            $table->string('bank_name')->nullable()->after('npwp');
            $table->string('bank_account')->nullable()->after('bank_name');
            $table->string('mother_maiden_name')->nullable()->after('bank_account');
            $table->string('marital_status')->nullable()->after('mother_maiden_name');
            $table->integer('children_count')->nullable()->after('marital_status');
            $table->string('certification_number')->nullable()->after('children_count');
            $table->string('certification_year')->nullable()->after('certification_number');
            $table->string('main_subject')->nullable()->after('certification_year');
            $table->integer('teaching_hours_per_week')->nullable()->after('main_subject');
            $table->string('sk_appointment')->nullable()->after('teaching_hours_per_week');
            $table->date('tmt_appointment')->nullable()->after('sk_appointment');
            $table->string('bpjs_number')->nullable()->after('tmt_appointment');
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'npwp','bank_name','bank_account','mother_maiden_name','marital_status','children_count',
                'certification_number','certification_year','main_subject','teaching_hours_per_week',
                'sk_appointment','tmt_appointment','bpjs_number'
            ]);
        });
    }
};








