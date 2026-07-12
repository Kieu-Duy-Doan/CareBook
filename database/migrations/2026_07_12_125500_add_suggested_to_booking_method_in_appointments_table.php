<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY COLUMN booking_method ENUM('doctor', 'specialty', 'suggested') NOT NULL DEFAULT 'doctor'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back
        DB::statement("ALTER TABLE appointments MODIFY COLUMN booking_method ENUM('doctor', 'specialty') NOT NULL DEFAULT 'doctor'");
    }
};
