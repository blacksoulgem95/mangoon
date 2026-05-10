<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_cookies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('source'); // nhentai, mangadex, etc.
            $table->json('cookies'); // Encrypted JSON array of cookies
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'source', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_cookies');
    }
};
