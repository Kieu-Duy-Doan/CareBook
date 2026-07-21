<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sepay_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->unique(); // ID từ SePay
            $table->string('gateway', 50)->nullable();
            $table->string('transaction_date')->nullable();
            $table->string('account_number')->nullable();
            $table->string('sub_account')->nullable();
            $table->decimal('amount_in', 15, 2)->default(0);
            $table->decimal('amount_out', 15, 2)->default(0);
            $table->decimal('accumulated', 15, 2)->default(0);
            $table->string('transaction_content')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('code')->nullable();
            $table->boolean('is_synced')->default(false); // Đã đối soát nội bộ chưa
            $table->unsignedBigInteger('matched_payment_id')->nullable();
            $table->enum('reconciliation_status', ['unmatched', 'matched', 'amount_mismatch', 'manual'])->default('unmatched');
            $table->text('reconciliation_note')->nullable();
            $table->foreign('matched_payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepay_transactions');
    }
};
