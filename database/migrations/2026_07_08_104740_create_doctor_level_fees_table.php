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
            ['level' => 'BS', 'base_price' => 100000, 'specific_price' => 200000],
            ['level' => 'BSCK1', 'base_price' => 150000, 'specific_price' => 250000],
            ['level' => 'BSCK2', 'base_price' => 200000, 'specific_price' => 300000],
            ['level' => 'ThS', 'base_price' => 200000, 'specific_price' => 300000],
            ['level' => 'TS', 'base_price' => 300000, 'specific_price' => 400000],
            ['level' => 'PGS', 'base_price' => 400000, 'specific_price' => 500000],
            ['level' => 'GS', 'base_price' => 500000, 'specific_price' => 600000],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_level_fees');
    }
};
