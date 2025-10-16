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
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('ip_address');
            $table->string('mac_address')->nullable();
            $table->string('user_agent');
            $table->string('attack_type'); // brute_force, ddos, sql_injection, xss, etc.
            $table->string('severity'); // low, medium, high, critical
            $table->text('description');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('attempt_count')->default(1);
            $table->timestamp('first_attempt')->nullable();
            $table->timestamp('last_attempt')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->index(['ip_address', 'created_at']);
            $table->index(['attack_type', 'created_at']);
            $table->index(['severity', 'created_at']);
            $table->index(['is_blocked', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_logs');
    }
};
