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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Role name (e.g., admin, editor, reader)');
            $table->string('slug')->unique()->comment('URL-friendly identifier');
            $table->text('description')->nullable()->comment('Description of the role');
            $table->string('guard_name')->default('web')->comment('Guard name for authorization');
            $table->integer('level')->default(0)->comment('Role hierarchy level (higher = more privileged)');
            $table->boolean('is_active')->default(true)->comment('Whether this role is active');
            $table->boolean('is_system')->default(false)->comment('Whether this is a system role (cannot be deleted)');
            $table->json('metadata')->nullable()->comment('Additional role metadata');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('guard_name');
            $table->index(['is_active', 'level']);
            $table->index('is_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
