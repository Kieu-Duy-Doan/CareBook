<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->restrictOnDelete();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->restrictOnDelete();
            $table->text('diagnosis');
            $table->string('icd10_code', 20)->nullable();
            $table->text('conclusion')->nullable();
            $table->text('advice')->nullable();
            $table->date('followup_date')->nullable();
            $table->enum('treatment_result', ['outpatient', 'admitted', 'monitoring'])->default('outpatient');
            $table->json('result_files')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
