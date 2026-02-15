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
        Schema::create('library_manga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manga_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0)->comment('Display order for manga in library');
            $table->boolean('is_featured')->default(false)->comment('Whether this manga is featured in this library');
            $table->timestamp('added_at')->useCurrent()->comment('When the manga was added to this library');
            $table->timestamps();

            // Unique constraint to prevent duplicate library assignments
            $table->unique(['library_id', 'manga_id']);

            // Indexes for efficient querying
            $table->index(['library_id', 'sort_order']);
            $table->index(['library_id', 'is_featured']);
            $table->index('manga_id');
            $table->index('added_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_manga');
    }
};
