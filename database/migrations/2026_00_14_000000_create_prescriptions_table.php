<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->unique()->constrained('medical_records')->restrictOnDelete();
            $table->date('prescribed_date');
            $table->text('diagnosis_note')->nullable();
            $table->json('items');
            $table->text('general_note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
