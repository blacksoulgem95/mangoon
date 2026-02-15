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
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('URL-friendly identifier');
            $table->string('type')->default('website')->comment('Type of source: publisher, website, scanlation, etc.');
            $table->string('url')->nullable()->comment('Official website URL');
            $table->string('country', 2)->nullable()->comment('ISO country code');
            $table->string('logo')->nullable()->comment('Logo path');
            $table->boolean('is_active')->default(true)->comment('Whether this source is active');
            $table->boolean('is_official')->default(false)->comment('Whether this is an official source');
            $table->json('metadata')->nullable()->comment('Additional source metadata');
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('type');
            $table->index(['is_active', 'is_official']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
