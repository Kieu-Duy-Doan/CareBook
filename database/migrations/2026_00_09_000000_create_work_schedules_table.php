<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->restrictOnDelete();
            $table->integer('room_id');
            $table->tinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->tinyInteger('slot_duration_minutes')->default(15);
            $table->tinyInteger('max_slots')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('rooms')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
