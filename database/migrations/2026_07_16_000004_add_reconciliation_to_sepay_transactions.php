<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sepay_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('matched_payment_id')->nullable()->after('is_synced');
            $table->enum('reconciliation_status', ['unmatched', 'matched', 'amount_mismatch', 'manual'])->default('unmatched')->after('matched_payment_id');
            $table->text('reconciliation_note')->nullable()->after('reconciliation_status');

            $table->foreign('matched_payment_id')->references('id')->on('payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sepay_transactions', function (Blueprint $table) {
            $table->dropForeign(['matched_payment_id']);
            $table->dropColumn(['matched_payment_id', 'reconciliation_status', 'reconciliation_note']);
        });
    }
};
