<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialty_rooms', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('specialty_id');
            $table->integer('room_id');
            $table->boolean('is_primary')->default(false);

            $table->foreign('specialty_id')->references('id')->on('specialties')->restrictOnDelete();
            $table->foreign('room_id')->references('id')->on('rooms')->restrictOnDelete();

            $table->unique(['specialty_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialty_rooms');
    }
};
