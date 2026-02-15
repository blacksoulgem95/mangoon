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
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Plugin display name');
            $table->string('slug')->unique()->comment('URL-friendly identifier');
            $table->string('class_name')->unique()->comment('Fully qualified class name');
            $table->string('version')->default('1.0.0')->comment('Plugin version');
            $table->text('description')->nullable()->comment('Plugin description');
            $table->longText('documentation')->nullable()->comment('Plugin documentation in markdown');
            $table->string('author')->nullable()->comment('Plugin author');
            $table->string('author_url')->nullable()->comment('Plugin author website');
            $table->string('icon')->nullable()->comment('Plugin icon path');

            // Configuration
            $table->json('config_schema')->nullable()->comment('JSON schema for plugin configuration');
            $table->json('config_values')->nullable()->comment('Current plugin configuration values');
            $table->json('default_config')->nullable()->comment('Default configuration values');

            // Status and type
            $table->boolean('is_active')->default(false)->comment('Whether this plugin is active');
            $table->boolean('is_installed')->default(false)->comment('Whether this plugin is installed');
            $table->boolean('is_system')->default(false)->comment('Whether this is a system plugin (cannot be deleted)');
            $table->string('type')->default('downloader')->comment('Plugin type: downloader, parser, scraper, etc.');

            // Plugin requirements and dependencies
            $table->string('requires_php')->nullable()->comment('Minimum PHP version required');
            $table->string('requires_laravel')->nullable()->comment('Minimum Laravel version required');
            $table->json('dependencies')->nullable()->comment('Other plugin dependencies');

            // Usage and statistics
            $table->unsignedBigInteger('downloads_count')->default(0)->comment('Number of manga downloaded using this plugin');
            $table->timestamp('last_used_at')->nullable()->comment('Last time this plugin was used');
            $table->timestamp('installed_at')->nullable()->comment('When this plugin was installed');

            // Priority and limits
            $table->integer('priority')->default(0)->comment('Plugin execution priority');
            $table->integer('rate_limit')->nullable()->comment('Rate limit per minute for this plugin');
            $table->integer('concurrent_limit')->default(1)->comment('Maximum concurrent downloads');

            // Additional metadata
            $table->json('metadata')->nullable()->comment('Additional plugin metadata');
            $table->json('supported_sources')->nullable()->comment('List of source IDs this plugin supports');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('class_name');
            $table->index('type');
            $table->index(['is_active', 'is_installed']);
            $table->index('priority');
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
