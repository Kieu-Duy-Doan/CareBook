<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('session_token', 100)->unique();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
