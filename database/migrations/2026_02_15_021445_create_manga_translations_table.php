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
        Schema::create('manga_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained()->cascadeOnDelete();
            $table->string('language_code', 10)->comment('ISO language code');
            $table->string('title')->comment('Translated manga title');
            $table->string('alternative_titles')->nullable()->comment('Alternative titles in this language');
            $table->text('synopsis')->nullable()->comment('Short synopsis');
            $table->longText('description')->nullable()->comment('Full description');
            $table->json('metadata')->nullable()->comment('Additional translated metadata');
            $table->timestamps();

            $table->unique(['manga_id', 'language_code']);
            $table->index('language_code');
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manga_translations');
    }
};
