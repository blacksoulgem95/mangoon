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
        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('URL-friendly identifier');
            $table->string('icon')->nullable()->comment('Icon for the library');
            $table->string('color', 7)->nullable()->comment('Hex color for the library theme');
            $table->boolean('is_active')->default(true)->comment('Whether this library is active');
            $table->boolean('is_public')->default(true)->comment('Whether this library is publicly accessible');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index(['is_active', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libraries');
    }
};
