<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->nullable()->index();
            $table->foreignId('user_id')->index()->constrained('users')->onDelete('cascade');
            $table->string('title', 255);
            $table->text('content');
            $table->enum('type', ['appointment', 'result', 'system', 'reminder', 'cancellation', 'system_cancellation', 'patient_cancellation', 'patient_booking', 'system_booking'])->index();
            $table->enum('channel', ['in_web', 'email', 'zalo'])->default('in_web');
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->boolean('is_sent')->default(false);
            $table->string('ref_type', 50)->nullable();
            $table->bigInteger('ref_id')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
