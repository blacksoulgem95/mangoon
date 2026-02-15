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
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_id')->nullable()->constrained()->cascadeOnDelete()->comment('Scope role to specific library');
            $table->timestamp('expires_at')->nullable()->comment('When this role assignment expires');
            $table->boolean('is_active')->default(true)->comment('Whether this role assignment is active');
            $table->json('metadata')->nullable()->comment('Additional role assignment metadata');
            $table->timestamps();

            // Unique constraint to prevent duplicate role assignments
            $table->unique(['role_id', 'user_id', 'library_id'], 'unique_role_user_library');

            // Indexes for efficient querying
            $table->index(['user_id', 'is_active']);
            $table->index(['role_id', 'is_active']);
            $table->index('library_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
