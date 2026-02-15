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
        Schema::create('category_manga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manga_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0)->comment('Display order for categories on manga');
            $table->timestamps();

            // Unique constraint to prevent duplicate category assignments
            $table->unique(['category_id', 'manga_id']);

            // Indexes for efficient querying
            $table->index(['manga_id', 'sort_order']);
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_manga');
    }
};
