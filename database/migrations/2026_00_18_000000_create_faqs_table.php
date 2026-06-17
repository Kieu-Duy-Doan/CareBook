<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->text('question');
            $table->text('answer');
            $table->integer('specialty_id')->nullable()->index();
            $table->string('keywords', 500)->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->foreign('specialty_id')->references('id')->on('specialties')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
