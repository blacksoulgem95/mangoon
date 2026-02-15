<?php

namespace App\Plugins;

use App\Contracts\PluginInterface;
use App\Models\Manga;
use App\Models\Source;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Abstract base class for Manga Download Plugins
 *
 * This class provides common functionality for all plugins.
 * Plugin developers should extend this class instead of implementing
 * the PluginInterface directly.
 */
abstract class AbstractPlugin implements PluginInterface
{
    /**
     * Plugin configuration.
     */
    protected array $config = [];

    /**
     * Plugin statistics.
     */
    protected array $statistics = [
        'downloads_count' => 0,
        'successful_downloads' => 0,
        'failed_downloads' => 0,
        'total_chapters_downloaded' => 0,
        'total_pages_downloaded' => 0,
        'last_download_at' => null,
        'errors' => [],
    ];

    /**
     * Whether the plugin is initialized.
     */
    protected bool $initialized = false;

    /**
     * Plugin name.
     */
    protected string $name = 'Generic Plugin';

    /**
     * Plugin version.
     */
    protected string $version = '1.0.0';

    /**
     * Plugin description.
     */
    protected string $description = 'A generic manga download plugin';

    /**
     * Plugin author.
     */
    protected string $author = 'Unknown';

    /**
     * Plugin author URL.
     */
    protected ?string $authorUrl = null;

    /**
     * Plugin icon path or URL.
     */
    protected ?string $icon = null;

    /**
     * Plugin type.
     */
    protected string $type = 'downloader';

    /**
     * Supported source IDs.
     *
     * @var array<int>
     */
    protected array $supportedSources = [];

    /**
     * Required PHP version.
     */
    protected ?string $requiresPhp = '8.3';

    /**
     * Required Laravel version.
     */
    protected ?string $requiresLaravel = '12.0';

    /**
     * Plugin dependencies.
     */
    protected array $dependencies = [];

    /**
     * Rate limit (requests per minute).
     */
    protected ?int $rateLimit = 60;

    /**
     * Maximum concurrent downloads.
     */
    protected int $concurrentLimit = 1;

    /**
     * Plugin priority (higher = executed first).
     */
    protected int $priority = 0;

    /**
     * Constructor.
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorUrl(): ?string
    {
        return $this->authorUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'enabled' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Enable or disable this plugin',
                ],
                'timeout' => [
                    'type' => 'integer',
                    'default' => 30,
                    'description' => 'Request timeout in seconds',
                ],
                'user_agent' => [
                    'type' => 'string',
                    'default' => 'Mozilla/5.0 (compatible; Mangoon/1.0)',
                    'description' => 'User agent string for HTTP requests',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig(): array
    {
        return [
            'enabled' => true,
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (compatible; Mangoon/1.0)',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentation(): string
    {
        return <<<MARKDOWN
# {$this->getName()}

Version: {$this->getVersion()}
Author: {$this->getAuthor()}

## Description

{$this->getDescription()}

## Installation

1. Ensure you have PHP {$this->getRequiredPhpVersion()} or higher installed
2. Ensure you have Laravel {$this->getRequiredLaravelVersion()} or higher installed
3. Install the plugin through the Mangoon plugin manager

## Configuration

Configure the plugin through the admin panel or by editing the configuration file.

## Usage

This plugin will automatically handle manga downloads from supported sources.

## Troubleshooting

If you encounter issues:

1. Check the plugin configuration
2. Verify your PHP and Laravel versions meet the requirements
3. Check the application logs for error messages
4. Test the plugin connection using the test button in the admin panel

## Support

For support, please contact: {$this->getAuthor()}
MARKDOWN;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedSources(): array
    {
        return $this->supportedSources;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsSource(Source|int $source): bool
    {
        $sourceId = $source instanceof Source ? $source->id : $source;

        return in_array($sourceId, $this->getSupportedSources(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredPhpVersion(): ?string
    {
        return $this->requiresPhp;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredLaravelVersion(): ?string
    {
        return $this->requiresLaravel;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function getRateLimit(): ?int
    {
        return $this->rateLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function getConcurrentLimit(): int
    {
        return $this->concurrentLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config): void
    {
        $this->validateConfig($config);
        $this->config = array_merge($this->config, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfig(array $config): bool
    {
        $schema = $this->getConfigSchema();

        if (! isset($schema['properties'])) {
            return true;
        }

        $rules = [];
        foreach ($schema['properties'] as $key => $property) {
            $rules[$key] = $this->convertSchemaToValidationRule($property);
        }

        $validator = Validator::make($config, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                'Invalid configuration: '.implode(', ', $validator->errors()->all())
            );
        }

        return true;
    }

    /**
     * Convert JSON schema property to Laravel validation rule.
     */
    protected function convertSchemaToValidationRule(array $property): string
    {
        $rules = ['nullable'];

        if (isset($property['type'])) {
            $rules[] = match ($property['type']) {
                'boolean' => 'boolean',
                'integer' => 'integer',
                'number' => 'numeric',
                'string' => 'string',
                'array' => 'array',
                default => 'string',
            };
        }

        return implode('|', $rules);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->log('info', 'Initializing plugin: '.$this->getName());
        $this->initialized = true;
    }

    /**
     * {@inheritdoc}
     */
    public function test(): array
    {
        try {
            $this->initialize();

            return [
                'success' => true,
                'message' => 'Plugin is working correctly',
                'data' => [
                    'name' => $this->getName(),
                    'version' => $this->getVersion(),
                    'initialized' => $this->initialized,
                    'config' => $this->getConfig(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Plugin test failed: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isReady(): bool
    {
        return $this->initialized && ($this->config['enabled'] ?? false);
    }

    /**
     * {@inheritdoc}
     */
    public function handleError(\Exception $exception): void
    {
        $this->statistics['errors'][] = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => now()->toISOString(),
        ];

        $this->log('error', 'Plugin error: '.$exception->getMessage(), [
            'exception' => $exception,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup(): void
    {
        $this->log('info', 'Cleaning up plugin: '.$this->getName());
        $this->initialized = false;
        $this->statistics = [
            'downloads_count' => 0,
            'successful_downloads' => 0,
            'failed_downloads' => 0,
            'total_chapters_downloaded' => 0,
            'total_pages_downloaded' => 0,
            'last_download_at' => null,
            'errors' => [],
        ];
    }

    /**
     * Log a message.
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        Log::$level('['.$this->getName().'] '.$message, $context);
    }

    /**
     * Update statistics after a download attempt.
     */
    protected function updateStatistics(bool $success, int $chaptersDownloaded = 0, int $pagesDownloaded = 0): void
    {
        $this->statistics['downloads_count']++;

        if ($success) {
            $this->statistics['successful_downloads']++;
        } else {
            $this->statistics['failed_downloads']++;
        }

        $this->statistics['total_chapters_downloaded'] += $chaptersDownloaded;
        $this->statistics['total_pages_downloaded'] += $pagesDownloaded;
        $this->statistics['last_download_at'] = now()->toISOString();
    }

    /**
     * Create a standardized success response.
     */
    protected function successResponse(string $message, array $data = [], ?Manga $manga = null): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'manga' => $manga,
        ];
    }

    /**
     * Create a standardized error response.
     */
    protected function errorResponse(string $message, array $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data,
            'manga' => null,
        ];
    }
}
