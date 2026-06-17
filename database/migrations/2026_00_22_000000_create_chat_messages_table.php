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
            $table->foreignId('session_id')->index()->constrained('chat_sessions')->restrictOnDelete();
            $table->enum('sender', ['user', 'bot']);
            $table->text('message');
            $table->integer('intent_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('intent_id')->references('id')->on('chatbot_intents')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
