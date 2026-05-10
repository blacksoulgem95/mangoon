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
        Schema::create('oauth_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider')->index();
            $table->string('provider_user_id')->index();
            $table->text('token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();
            
            $table->unique(['provider', 'provider_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_accounts');
    }
};
