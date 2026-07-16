<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            
            $table->string('transaction_code', 100)->unique(); // SePay reference hoặc mã tự sinh khi thu tiền mặt
            $table->string('intent_code')->nullable(); // Mã QR dùng một lần
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['qr', 'cash', 'insurance', 'waived']);
            $table->enum('status', ['pending', 'completed', 'refunded', 'needs_review'])->default('completed');
            
            $table->string('sepay_reference')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->text('note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
