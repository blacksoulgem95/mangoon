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
        Schema::create('manga_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0)->comment('Display order for tags on manga');
            $table->timestamps();

            // Unique constraint to prevent duplicate tag assignments
            $table->unique(['manga_id', 'tag_id']);

            // Indexes for efficient querying
            $table->index(['manga_id', 'sort_order']);
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manga_tag');
    }
};
