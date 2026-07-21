<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_code', 20)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->restrictOnDelete();
            $table->foreignId('booked_by_user_id')->constrained('users')->restrictOnDelete();
            $table->integer('specialty_id');
            $table->enum('doctor_level', ['BS', 'BSCK1', 'BSCK2', 'ThS', 'TS', 'PGS', 'GS'])->nullable();
            $table->integer('room_id');
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->restrictOnDelete();
            $table->date('appointment_date')->index();
            $table->time('appointment_time');
            $table->text('reason');
            $table->enum('status', ['pending', 'checked_in', 'examining', 'completed', 'cancelled', 'absent', 'late'])->default('pending')->index();
            $table->decimal('total_fee', 15, 2)->nullable();
            $table->boolean('reminded_2h')->default(false);
            $table->boolean('reminded_30m')->default(false);
            $table->enum('source', ['web', 'counter', 'chatbot'])->default('web');
            $table->enum('booking_method', ['doctor', 'specialty', 'suggested'])->default('doctor');
            $table->text('receptionist_note')->nullable();
            $table->smallInteger('vital_pulse')->nullable();
            $table->smallInteger('vital_systolic_bp')->nullable();
            $table->smallInteger('vital_diastolic_bp')->nullable();
            $table->decimal('vital_temperature', 4, 1)->nullable();
            $table->smallInteger('vital_respiratory')->nullable();
            $table->decimal('vital_spo2', 4, 1)->nullable();
            $table->decimal('vital_weight_kg', 5, 2)->nullable();
            $table->decimal('vital_height_cm', 5, 2)->nullable();
            $table->decimal('vital_bmi', 5, 2)->nullable();
            $table->text('vital_note')->nullable();
            $table->foreignId('measured_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('specialty_id')->references('id')->on('specialties')->restrictOnDelete();
            $table->foreign('room_id')->references('id')->on('rooms')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
