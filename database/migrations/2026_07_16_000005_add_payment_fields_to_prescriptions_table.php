<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->decimal('payment_amount', 12, 2)->default(0)->after('general_note');
            $table->enum('payment_status', ['pending', 'paid', 'waived'])->default('pending')->index()->after('payment_amount');
            $table->enum('payment_method', ['qr', 'cash', 'insurance', 'waived'])->nullable()->after('payment_status');
            $table->foreignId('collected_by')->nullable()->constrained('users')->restrictOnDelete()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('collected_by');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['collected_by']);
            $table->dropColumn(['payment_amount', 'payment_status', 'payment_method', 'collected_by', 'paid_at']);
        });
    }
};
