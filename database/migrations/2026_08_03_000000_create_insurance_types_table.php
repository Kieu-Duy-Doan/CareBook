<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_types', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 5)->unique()->comment('Mã đầu thẻ BHYT, vd: TE, HT, DN');
            $table->string('name', 100)->comment('Tên loại BHYT');
            $table->tinyInteger('coverage_percent')->default(80)->comment('Tỷ lệ chi trả BHYT (0-100)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_types');
    }
};
