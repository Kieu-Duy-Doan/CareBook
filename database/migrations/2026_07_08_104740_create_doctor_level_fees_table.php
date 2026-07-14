<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_level_fees', function (Blueprint $table) {
            $table->id();
            $table->string('level')->unique();
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('specific_price', 15, 2)->default(0);
            $table->timestamps();
        });

        // Insert default data
        DB::table('doctor_level_fees')->insert([
            ['level' => 'BS', 'base_price' => 2000, 'specific_price' => 3000],
            ['level' => 'BSCK1', 'base_price' => 3000, 'specific_price' => 4000],
            ['level' => 'BSCK2', 'base_price' => 4000, 'specific_price' => 5000],
            ['level' => 'ThS', 'base_price' => 5000, 'specific_price' => 6000],
            ['level' => 'TS', 'base_price' => 6000, 'specific_price' => 7000],
            ['level' => 'PGS', 'base_price' => 7000, 'specific_price' => 8000],
            ['level' => 'GS', 'base_price' => 8000, 'specific_price' => 10000],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_level_fees');
    }
};
