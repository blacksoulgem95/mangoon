<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("categories", function (Blueprint $table) {
            $table->id();
            $table
                ->string("slug", 191)
                ->unique()
                ->comment("URL-friendly identifier");
            $table
                ->foreignId("parent_id")
                ->nullable()
                ->constrained("categories")
                ->nullOnDelete()
                ->comment("Parent category for hierarchical structure");
            $table
                ->string("icon")
                ->nullable()
                ->comment("Icon for the category");
            $table
                ->string("color", 7)
                ->nullable()
                ->comment("Hex color for the category");
            $table
                ->boolean("is_active")
                ->default(true)
                ->comment("Whether this category is active");
            $table->integer("sort_order")->default(0)->comment("Display order");
            $table->timestamps();
            $table->softDeletes();

            $table->index("slug");
            $table->index("parent_id");
            $table->index("is_active");
            $table->index("sort_order");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("categories");
    }
};
