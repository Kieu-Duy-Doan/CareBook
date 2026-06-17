<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_overrides', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->restrictOnDelete();
            $table->integer('room_id')->nullable();
            $table->date('override_date')->index();
            $table->enum('type', ['close', 'extra']);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('reason', 255)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('room_id')->references('id')->on('rooms')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_overrides');
    }
};
