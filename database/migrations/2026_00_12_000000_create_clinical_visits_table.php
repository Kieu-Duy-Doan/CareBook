<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->restrictOnDelete();
            $table->foreignId('parent_visit_id')->nullable()->constrained('clinical_visits')->restrictOnDelete();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->restrictOnDelete();
            $table->integer('room_id');
            $table->tinyInteger('visit_order');
            $table->boolean('is_origin')->default(false);
            $table->text('findings')->nullable();
            $table->json('result_files')->nullable();
            $table->text('refusal_reason')->nullable();
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'refused', 'redirected'])->default('waiting')->index();
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'waived'])->default('pending')->index();
            $table->enum('payment_method', ['qr', 'cash', 'insurance', 'waived'])->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('rooms')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_visits');
    }
};
