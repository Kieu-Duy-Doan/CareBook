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
        // Xoá dữ liệu cũ để tránh lỗi khoá ngoại
        \Illuminate\Support\Facades\DB::table('payments')->truncate();

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['clinical_visit_id']);
            $table->dropColumn('clinical_visit_id');
            $table->foreignId('appointment_id')->after('id')->constrained('appointments')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
            $table->foreignId('clinical_visit_id')->after('id')->constrained('clinical_visits')->restrictOnDelete();
        });
    }
};
