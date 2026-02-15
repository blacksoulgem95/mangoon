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
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true)->comment('Whether this permission assignment is active');
            $table->json('metadata')->nullable()->comment('Additional permission assignment metadata');
            $table->timestamps();

            // Unique constraint to prevent duplicate permission assignments
            $table->unique(['permission_id', 'role_id'], 'unique_permission_role');

            // Indexes for efficient querying
            $table->index(['role_id', 'is_active']);
            $table->index(['permission_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
