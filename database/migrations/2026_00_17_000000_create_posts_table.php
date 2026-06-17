<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('title', 255);
            $table->string('slug', 300)->unique();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('thumbnail_url', 500)->nullable();
            $table->integer('specialty_id')->nullable()->index();
            $table->enum('post_type', ['news', 'service', 'guide', 'announcement'])->default('news')->index();
            $table->integer('view_count')->default(0);
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('specialty_id')->references('id')->on('specialties')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
