<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('full_name', 100);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('id_card', 20)->nullable()->unique();
            $table->string('phone', 15)->nullable();
            $table->text('address')->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('ethnicity', 50)->nullable();
            $table->string('insurance_code', 20)->nullable();
            $table->string('insurance_place', 255)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->json('medical_history')->nullable();
            $table->text('symptom_notes')->nullable();
            $table->boolean('is_self')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_profiles');
    }
};
