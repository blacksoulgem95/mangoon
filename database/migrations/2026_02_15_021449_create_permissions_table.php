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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Permission name (e.g., view mangas, edit mangas)');
            $table->string('slug')->unique()->comment('URL-friendly identifier');
            $table->text('description')->nullable()->comment('Description of the permission');
            $table->string('guard_name')->default('web')->comment('Guard name for authorization');
            $table->string('resource')->nullable()->comment('Resource this permission applies to (manga, library, etc.)');
            $table->string('action')->nullable()->comment('Action allowed (view, create, edit, delete, etc.)');
            $table->string('scope')->default('global')->comment('Scope: global, library, own');
            $table->boolean('is_active')->default(true)->comment('Whether this permission is active');
            $table->boolean('is_system')->default(false)->comment('Whether this is a system permission (cannot be deleted)');
            $table->json('metadata')->nullable()->comment('Additional permission metadata');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('guard_name');
            $table->index(['resource', 'action']);
            $table->index('scope');
            $table->index(['is_active', 'is_system']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
