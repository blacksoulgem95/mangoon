<?php

namespace App\Contracts;

use App\Models\Manga;
use App\Models\Source;

/**
 * Interface for Manga Download Plugins
 *
 * All manga download plugins must implement this interface to be integrated
 * into the Mangoon system.
 */
interface PluginInterface
{
    /**
     * Get the plugin name.
     */
    public function getName(): string;

    /**
     * Get the plugin version.
     */
    public function getVersion(): string;

    /**
     * Get the plugin description.
     */
    public function getDescription(): string;

    /**
     * Get the plugin author.
     */
    public function getAuthor(): string;

    /**
     * Get the plugin author URL.
     */
    public function getAuthorUrl(): ?string;

    /**
     * Get the plugin icon path or URL.
     */
    public function getIcon(): ?string;

    /**
     * Get the plugin type.
     *
     * @return string (downloader, parser, scraper, etc.)
     */
    public function getType(): string;

    /**
     * Get the configuration schema for this plugin.
     *
     * Returns a JSON schema defining the configuration options
     * that can be set for this plugin.
     */
    public function getConfigSchema(): array;

    /**
     * Get the default configuration values.
     */
    public function getDefaultConfig(): array;

    /**
     * Get the plugin documentation in Markdown format.
     *
     * This should include installation instructions, configuration guide,
     * usage examples, and troubleshooting tips.
     */
    public function getDocumentation(): string;

    /**
     * Get the list of supported source IDs.
     *
     * @return array<int>
     */
    public function getSupportedSources(): array;

    /**
     * Check if this plugin supports a specific source.
     */
    public function supportsSource(Source|int $source): bool;

    /**
     * Get the required PHP version.
     */
    public function getRequiredPhpVersion(): ?string;

    /**
     * Get the required Laravel version.
     */
    public function getRequiredLaravelVersion(): ?string;

    /**
     * Get the plugin dependencies (other plugins required).
     */
    public function getDependencies(): array;

    /**
     * Get the rate limit (requests per minute) for this plugin.
     */
    public function getRateLimit(): ?int;

    /**
     * Get the maximum concurrent downloads for this plugin.
     */
    public function getConcurrentLimit(): int;

    /**
     * Get the priority of this plugin (higher = executed first).
     */
    public function getPriority(): int;

    /**
     * Set the plugin configuration.
     */
    public function setConfig(array $config): void;

    /**
     * Get the current plugin configuration.
     */
    public function getConfig(): array;

    /**
     * Validate the plugin configuration.
     *
     * @throws \InvalidArgumentException
     */
    public function validateConfig(array $config): bool;

    /**
     * Initialize the plugin.
     *
     * Called when the plugin is loaded or activated.
     */
    public function initialize(): void;

    /**
     * Test the plugin connection and functionality.
     *
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function test(): array;

    /**
     * Download manga from a specific URL or source.
     *
     * @param  string  $url  The manga URL to download from
     * @param  array  $options  Additional download options
     * @return array ['success' => bool, 'message' => string, 'data' => array, 'manga' => Manga|null]
     */
    public function download(string $url, array $options = []): array;

    /**
     * Parse manga metadata from a URL.
     *
     * @param  string  $url  The manga URL to parse
     * @return array Manga metadata
     */
    public function parseMetadata(string $url): array;

    /**
     * Search for manga on the source.
     *
     * @param  string  $query  Search query
     * @param  array  $filters  Additional search filters
     * @return array Search results
     */
    public function search(string $query, array $filters = []): array;

    /**
     * Get manga chapters list.
     *
     * @param  string  $url  The manga URL
     * @return array List of chapters
     */
    public function getChapters(string $url): array;

    /**
     * Download a specific chapter.
     *
     * @param  string  $chapterUrl  The chapter URL
     * @param  array  $options  Download options
     * @return array ['success' => bool, 'message' => string, 'pages' => array]
     */
    public function downloadChapter(string $chapterUrl, array $options = []): array;

    /**
     * Check if the plugin is properly configured and ready to use.
     */
    public function isReady(): bool;

    /**
     * Handle plugin errors.
     */
    public function handleError(\Exception $exception): void;

    /**
     * Get plugin statistics.
     */
    public function getStatistics(): array;

    /**
     * Clean up resources before uninstalling the plugin.
     */
    public function cleanup(): void;
}
