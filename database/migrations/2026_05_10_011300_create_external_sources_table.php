<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained()->onDelete('cascade');
            $table->string('source_name'); // nhentai, mangadex, etc.
            $table->string('source_id')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            $table->unique(['source_name', 'source_id']);
            $table->index(['manga_id', 'source_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_sources');
    }
};
