<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('birth_certificate_number')->nullable()->after('nik');
            $table->string('kk_number')->nullable()->after('birth_certificate_number');
            $table->string('kks_number')->nullable()->nullable()->after('kk_number');
            $table->string('kip_number')->nullable()->after('kks_number');
            $table->string('pkh_number')->nullable()->after('kip_number');
            $table->string('citizenship')->nullable()->after('religion');
            $table->string('residence_type')->nullable()->after('transportation');
            $table->integer('sibling_count')->nullable()->after('residence_type');
            $table->integer('order_in_family')->nullable()->after('sibling_count');
            $table->string('special_needs')->nullable()->after('order_in_family');
            $table->string('blood_type')->nullable()->after('special_needs');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'birth_certificate_number','kk_number','kks_number','kip_number','pkh_number','citizenship',
                'residence_type','sibling_count','order_in_family','special_needs','blood_type'
            ]);
        });
    }
};



