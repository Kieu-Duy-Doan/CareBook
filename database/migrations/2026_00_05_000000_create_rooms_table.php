<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name', 150);
            $table->string('room_number', 20)->nullable();
            $table->string('building', 50)->nullable();
            $table->string('floor', 10)->nullable();
            $table->enum('room_type', ['examination', 'diagnostic', 'surgery', 'other'])->default('examination');
            $table->integer('price')->nullable()->comment('Giá tiền dịch vụ phòng (nếu là phòng diagnostic)');
            $table->tinyInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
