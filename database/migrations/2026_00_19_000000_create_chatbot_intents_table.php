<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_intents', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('intent_name', 100)->unique();
            $table->string('description', 255);
            $table->text('example_phrases')->nullable();
            $table->enum('action', ['faq_lookup', 'guide_booking', 'introduce_specialty', 'transfer_staff']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('chatbot_intents');
    }
};
