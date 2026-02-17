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
        Schema::create("mangas", function (Blueprint $table) {
            $table->id();
            $table
                ->string("slug")
                ->unique()
                ->comment("URL-friendly identifier");
            $table
                ->foreignId("source_id")
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->comment("Primary source of the manga");

            // Metadata fields
            $table->string("author")->nullable()->comment("Manga author(s)");
            $table
                ->string("illustrator")
                ->nullable()
                ->comment("Manga illustrator(s)");
            $table
                ->year("publication_year")
                ->nullable()
                ->comment("Year of first publication");
            $table
                ->date("publication_date")
                ->nullable()
                ->comment("Exact publication date if known");
            $table
                ->string("original_language", 10)
                ->nullable()
                ->comment("Original language code");

            // Status and type
            $table
                ->enum("status", [
                    "ongoing",
                    "completed",
                    "hiatus",
                    "cancelled",
                    "upcoming",
                ])
                ->default("ongoing")
                ->comment("Publication status");
            $table
                ->enum("type", [
                    "manga",
                    "manhwa",
                    "manhua",
                    "webtoon",
                    "novel",
                    "other",
                ])
                ->default("manga")
                ->comment("Type of publication");

            // Media
            $table
                ->string("cover_image")
                ->nullable()
                ->comment("Cover image path");
            $table
                ->string("banner_image")
                ->nullable()
                ->comment("Banner image path");

            // Metrics
            $table
                ->unsignedInteger("total_chapters")
                ->default(0)
                ->comment("Total number of chapters");
            $table
                ->unsignedInteger("total_volumes")
                ->default(0)
                ->comment("Total number of volumes");
            $table
                ->decimal("rating", 3, 2)
                ->nullable()
                ->comment("Average rating (0.00-10.00)");
            $table
                ->unsignedBigInteger("views_count")
                ->default(0)
                ->comment("Total views count");
            $table
                ->unsignedBigInteger("favorites_count")
                ->default(0)
                ->comment("Total favorites count");

            // Publishing info
            $table->string("isbn")->nullable()->comment("ISBN if available");
            $table
                ->string("publisher")
                ->nullable()
                ->comment("Original publisher");

            // Additional metadata
            $table
                ->json("metadata")
                ->nullable()
                ->comment("Additional flexible metadata");

            // Flags
            $table
                ->boolean("is_active")
                ->default(true)
                ->comment("Whether this manga is active");
            $table
                ->boolean("is_featured")
                ->default(false)
                ->comment("Whether this manga is featured");
            $table
                ->boolean("is_mature")
                ->default(false)
                ->comment("Whether this manga contains mature content");

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index("slug");
            $table->index("source_id");
            $table->index("status");
            $table->index("type");
            $table->index("original_language");
            $table->index("is_active");
            $table->index("is_featured");
            $table->index("publication_year");
            $table->index("rating");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("mangas");
    }
};
