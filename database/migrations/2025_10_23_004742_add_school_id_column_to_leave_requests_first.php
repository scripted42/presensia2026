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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Add school_id column if it doesn't exist
            if (!Schema::hasColumn('leave_requests', 'school_id')) {
                $table->unsignedBigInteger('school_id')->nullable()->after('user_id');
            }
        });
        
        // Update existing records with school_id from user
        \DB::statement('UPDATE leave_requests lr 
            JOIN users u ON lr.user_id = u.id 
            SET lr.school_id = u.school_id 
            WHERE lr.school_id IS NULL');
        
        // Make school_id not nullable
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('school_id');
        });
    }
};
