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
        // First, update any null school_id values
        \DB::statement('UPDATE leave_requests lr 
            JOIN users u ON lr.user_id = u.id 
            SET lr.school_id = u.school_id 
            WHERE lr.school_id IS NULL OR lr.school_id = 0');
        
        // Delete any leave_requests that don't have valid school_id
        \DB::statement('DELETE lr FROM leave_requests lr 
            LEFT JOIN schools s ON lr.school_id = s.id 
            WHERE s.id IS NULL');
        
        Schema::table('leave_requests', function (Blueprint $table) {
            // Add foreign key constraint if it doesn't exist
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->index(['school_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id', 'status']);
        });
    }
};
