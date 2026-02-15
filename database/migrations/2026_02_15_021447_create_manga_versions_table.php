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
        Schema::create('manga_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained()->cascadeOnDelete()->comment('The manga entry');
            $table->foreignId('related_manga_id')->constrained('mangas')->cascadeOnDelete()->comment('The related manga version');
            $table->string('relationship_type')->default('translation')->comment('Type: translation, adaptation, spin-off, etc.');
            $table->string('language_code', 10)->nullable()->comment('Language of the related version');
            $table->text('notes')->nullable()->comment('Notes about this version relationship');
            $table->integer('sort_order')->default(0)->comment('Display order for versions');
            $table->boolean('is_primary')->default(false)->comment('Whether this is the primary version');
            $table->timestamps();

            // Ensure unique relationships
            $table->unique(['manga_id', 'related_manga_id', 'relationship_type'], 'unique_manga_version');

            // Indexes for efficient querying
            $table->index(['manga_id', 'relationship_type']);
            $table->index(['related_manga_id', 'relationship_type']);
            $table->index('language_code');
            $table->index(['is_primary', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manga_versions');
    }
};
