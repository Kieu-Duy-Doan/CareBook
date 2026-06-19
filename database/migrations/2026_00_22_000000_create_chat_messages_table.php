<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->index()->constrained('chat_sessions')->onDelete('cascade');
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->string('intent_detected', 100)->nullable();
            $table->boolean('is_flagged')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
