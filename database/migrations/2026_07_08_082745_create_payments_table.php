<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinical_visit_id')->constrained('clinical_visits')->restrictOnDelete();
            $table->unsignedBigInteger('order_code')->unique()->comment('Code gửi lên PayOS');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'insurance', 'payos']);
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->string('transaction_id')->nullable()->comment('Mã giao dịch từ đối tác');
            $table->foreignId('collected_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
