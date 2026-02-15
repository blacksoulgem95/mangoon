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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('chapter_number', 50);
            $table->string('title')->nullable();
            $table->integer('volume_number')->nullable();
            $table->text('notes')->nullable();
            $table->date('release_date')->nullable();
            $table->string('cbz_file_path');
            $table->string('storage_disk')->default('public');
            $table->bigInteger('file_size')->nullable();
            $table->integer('page_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['manga_id', 'chapter_number']);
            $table->index(['manga_id', 'sort_order']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
