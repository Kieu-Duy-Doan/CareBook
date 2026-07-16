<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('doctor_code', 20)->unique();
            $table->enum('academic_rank', ['none', 'PGS', 'GS'])->default('none');
            $table->enum('degree', ['BS', 'ThS', 'TS', 'BSCK1', 'BSCK2', 'BSNT'])->default('BS');
            $table->enum('current_position', ['INTERN', 'ATTENDING', 'CONSULTANT', 'DEPARTMENT_HEAD', 'EXPERT'])->default('ATTENDING');
            $table->enum('level', ['BS', 'BSCK1', 'BSCK2', 'ThS', 'TS', 'PGS', 'GS'])->default('BS');
            $table->text('expertise')->nullable();
            $table->tinyInteger('experience_years')->nullable();
            $table->string('license_number', 50)->nullable()->unique();
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
