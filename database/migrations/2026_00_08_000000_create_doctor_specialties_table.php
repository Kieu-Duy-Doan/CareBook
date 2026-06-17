<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_specialties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->restrictOnDelete();
            $table->integer('specialty_id');
            $table->boolean('is_primary')->default(false);

            $table->foreign('specialty_id')->references('id')->on('specialties')->restrictOnDelete();
            $table->unique(['doctor_profile_id', 'specialty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_specialties');
    }
};
