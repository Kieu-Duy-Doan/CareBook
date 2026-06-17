<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_responses', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('intent_id')->index();
            $table->text('content');
            $table->tinyInteger('priority')->default(1);
            $table->integer('use_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('intent_id')->references('id')->on('chatbot_intents')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_responses');
    }
};
