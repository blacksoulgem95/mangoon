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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('ISO language code (e.g., en, it, ja)');
            $table->string('name')->comment('Language name in English');
            $table->string('native_name')->comment('Language name in its native form');
            $table->boolean('is_active')->default(true)->comment('Whether this language is active');
            $table->boolean('is_default')->default(false)->comment('Whether this is the default language');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
