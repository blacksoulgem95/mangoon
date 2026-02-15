# Plugin Development Guide for Mangoon

## Table of Contents

- [Introduction](#introduction)
- [Plugin Architecture](#plugin-architecture)
- [Getting Started](#getting-started)
- [Creating Your First Plugin](#creating-your-first-plugin)
- [Plugin Interface Reference](#plugin-interface-reference)
- [Configuration Schema](#configuration-schema)
- [Testing Your Plugin](#testing-your-plugin)
- [Best Practices](#best-practices)
- [Advanced Topics](#advanced-topics)
- [Example Plugins](#example-plugins)
- [Troubleshooting](#troubleshooting)
- [API Reference](#api-reference)

## Introduction

Mangoon's plugin system allows you to extend the application's manga downloading capabilities by creating custom plugins that can fetch manga from various sources. Each plugin is a self-contained, configurable module that implements the `PluginInterface`.

### What is a Plugin?

A plugin is a PHP class that:
- Extends `AbstractPlugin` or implements `PluginInterface`
- Provides methods to download, parse, and search manga from a specific source
- Defines its own configuration schema
- Can be installed, activated, and configured through the admin panel

### Why Create a Plugin?

- Add support for new manga sources
- Customize download behavior
- Implement specialized scrapers or parsers
- Integrate with external APIs
- Share your plugin with the community

## Plugin Architecture

### Core Components

```
App/
├── Contracts/
│   └── PluginInterface.php       # Plugin contract
├── Plugins/
│   ├── AbstractPlugin.php        # Base class for plugins
│   ├── MangaDexPlugin.php        # Example plugin
│   └── YourCustomPlugin.php      # Your plugin here
└── Services/
    └── PluginManager.php         # Plugin lifecycle manager
```

### Plugin Lifecycle

1. **Registration**: Plugin class is registered with the system
2. **Installation**: Plugin is installed and initialized
3. **Activation**: Plugin is enabled for use
4. **Execution**: Plugin methods are called to perform operations
5. **Deactivation**: Plugin is disabled
6. **Uninstallation**: Plugin cleanup and removal

### Data Flow

```
User Request → PluginManager → Plugin Instance → External API → Data Processing → Manga Creation
```

## Getting Started

### Prerequisites

- PHP 8.3 or higher
- Understanding of Laravel framework
- Familiarity with HTTP requests and web scraping (if applicable)
- Knowledge of the target manga source's structure

### Development Environment Setup

1. Clone the Mangoon repository
2. Install dependencies: `composer install`
3. Set up your local database
4. Run migrations: `php artisan migrate`

### Plugin Directory Structure

Create your plugin file in `app/Plugins/`:

```
app/Plugins/
└── MySourcePlugin.php
```

## Creating Your First Plugin

### Step 1: Create the Plugin Class

Create a new file `app/Plugins/MySourcePlugin.php`:

```php
<?php

namespace App\Plugins;

use App\Models\Manga;
use Illuminate\Support\Facades\Http;

class MySourcePlugin extends AbstractPlugin
{
    /**
     * Plugin metadata
     */
    protected string $name = 'My Source Plugin';
    protected string $version = '1.0.0';
    protected string $description = 'Downloads manga from MySource';
    protected string $author = 'Your Name';
    protected ?string $authorUrl = 'https://yourwebsite.com';
    protected ?string $icon = '/images/plugins/mysource.png';
    protected string $type = 'downloader';
    
    /**
     * Plugin configuration
     */
    protected ?int $rateLimit = 60; // 60 requests per minute
    protected int $concurrentLimit = 1;
    protected int $priority = 10;
    
    /**
     * Source URL
     */
    protected string $baseUrl = 'https://mysource.com';
}
```

### Step 2: Implement Required Methods

#### Initialize Method

```php
public function initialize(): void
{
    parent::initialize();
    
    // Your initialization logic here
    $this->log('info', 'MySource plugin initialized');
}
```

#### Download Method

```php
public function download(string $url, array $options = []): array
{
    try {
        $this->initialize();
        
        // Extract manga ID from URL
        $mangaId = $this->extractMangaId($url);
        
        if (!$mangaId) {
            return $this->errorResponse('Invalid URL format');
        }
        
        // Parse metadata
        $metadata = $this->parseMetadata($url);
        
        if (empty($metadata)) {
            return $this->errorResponse('Failed to parse manga metadata');
        }
        
        // Create manga in database
        $manga = $this->createManga($metadata);
        
        if (!$manga) {
            return $this->errorResponse('Failed to create manga entry');
        }
        
        // Update statistics
        $this->updateStatistics(true);
        
        return $this->successResponse(
            'Manga downloaded successfully',
            ['manga_id' => $manga->id],
            $manga
        );
        
    } catch (\Exception $e) {
        $this->handleError($e);
        $this->updateStatistics(false);
        return $this->errorResponse('Download failed: ' . $e->getMessage());
    }
}
```

#### Parse Metadata Method

```php
public function parseMetadata(string $url): array
{
    try {
        $mangaId = $this->extractMangaId($url);
        
        // Fetch manga data
        $response = Http::timeout($this->config['timeout'] ?? 30)
            ->withHeaders([
                'User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0',
            ])
            ->get("{$this->baseUrl}/api/manga/{$mangaId}");
        
        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch manga data');
        }
        
        $data = $response->json();
        
        // Transform data to Mangoon format
        return [
            'external_id' => $mangaId,
            'title' => $data['title'] ?? 'Unknown',
            'slug' => \Str::slug($data['title'] ?? 'unknown'),
            'author' => $data['author'] ?? null,
            'illustrator' => $data['artist'] ?? null,
            'description' => $data['description'] ?? null,
            'cover_image' => $data['cover'] ?? null,
            'status' => $this->mapStatus($data['status'] ?? 'unknown'),
            'type' => 'manga',
            'publication_year' => $data['year'] ?? null,
            'tags' => $data['tags'] ?? [],
        ];
        
    } catch (\Exception $e) {
        $this->handleError($e);
        return [];
    }
}
```

#### Search Method

```php
public function search(string $query, array $filters = []): array
{
    try {
        $this->initialize();
        
        $response = Http::timeout($this->config['timeout'] ?? 30)
            ->withHeaders([
                'User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0',
            ])
            ->get("{$this->baseUrl}/api/search", [
                'q' => $query,
                'limit' => $filters['limit'] ?? 20,
                'offset' => $filters['offset'] ?? 0,
            ]);
        
        if (!$response->successful()) {
            return [];
        }
        
        $results = $response->json('results', []);
        
        return array_map([$this, 'transformSearchResult'], $results);
        
    } catch (\Exception $e) {
        $this->handleError($e);
        return [];
    }
}
```

#### Get Chapters Method

```php
public function getChapters(string $url): array
{
    try {
        $this->initialize();
        
        $mangaId = $this->extractMangaId($url);
        
        $response = Http::timeout($this->config['timeout'] ?? 30)
            ->withHeaders([
                'User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0',
            ])
            ->get("{$this->baseUrl}/api/manga/{$mangaId}/chapters");
        
        if (!$response->successful()) {
            return [];
        }
        
        $chapters = $response->json('chapters', []);
        
        return array_map(function ($chapter) {
            return [
                'id' => $chapter['id'],
                'chapter' => $chapter['number'],
                'title' => $chapter['title'] ?? null,
                'volume' => $chapter['volume'] ?? null,
                'language' => $chapter['language'] ?? 'en',
                'pages' => $chapter['pages'] ?? 0,
                'url' => "{$this->baseUrl}/chapter/{$chapter['id']}",
            ];
        }, $chapters);
        
    } catch (\Exception $e) {
        $this->handleError($e);
        return [];
    }
}
```

#### Download Chapter Method

```php
public function downloadChapter(string $chapterUrl, array $options = []): array
{
    try {
        $this->initialize();
        
        $chapterId = $this->extractChapterId($chapterUrl);
        
        // Fetch chapter data
        $response = Http::timeout($this->config['timeout'] ?? 30)
            ->withHeaders([
                'User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0',
            ])
            ->get("{$this->baseUrl}/api/chapter/{$chapterId}");
        
        if (!$response->successful()) {
            return $this->errorResponse('Failed to fetch chapter data');
        }
        
        $data = $response->json();
        $pages = $data['pages'] ?? [];
        
        // Get image URLs
        $imageUrls = array_map(function ($page) {
            return $page['url'];
        }, $pages);
        
        $this->updateStatistics(true, 1, count($imageUrls));
        
        return $this->successResponse('Chapter downloaded', [
            'chapter_id' => $chapterId,
            'pages' => $imageUrls,
            'page_count' => count($imageUrls),
        ]);
        
    } catch (\Exception $e) {
        $this->handleError($e);
        $this->updateStatistics(false);
        return $this->errorResponse('Chapter download failed: ' . $e->getMessage());
    }
}
```

### Step 3: Add Configuration Schema

```php
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
            'api_key' => [
                'type' => 'string',
                'default' => '',
                'description' => 'API key for authentication (if required)',
            ],
            'base_url' => [
                'type' => 'string',
                'default' => 'https://mysource.com',
                'description' => 'Base URL for the manga source',
            ],
            'timeout' => [
                'type' => 'integer',
                'default' => 30,
                'description' => 'Request timeout in seconds',
            ],
            'preferred_language' => [
                'type' => 'string',
                'default' => 'en',
                'description' => 'Preferred language for downloads',
            ],
        ],
    ];
}

public function getDefaultConfig(): array
{
    return [
        'enabled' => true,
        'api_key' => '',
        'base_url' => 'https://mysource.com',
        'timeout' => 30,
        'preferred_language' => 'en',
    ];
}
```

### Step 4: Add Documentation

```php
public function getDocumentation(): string
{
    return <<<MARKDOWN
# MySource Plugin

Version: {$this->getVersion()}
Author: {$this->getAuthor()}

## Description

This plugin allows downloading manga from MySource.

## Installation

1. Ensure you have an API key from MySource (if required)
2. Install the plugin through the admin panel
3. Configure the plugin with your API key

## Configuration

### API Key

If the source requires authentication, enter your API key in the configuration.

### Base URL

The base URL for the manga source. Default: https://mysource.com

### Timeout

Request timeout in seconds. Increase if experiencing timeout errors.

### Preferred Language

Default language for manga downloads.

## Usage

1. Copy a manga URL from MySource
2. Go to Manga Management → Download from Source
3. Paste the URL and click Download

## Supported URL Formats

- https://mysource.com/manga/{id}
- https://mysource.com/title/{id}/{title}

## Troubleshooting

### "Invalid URL format" error

Ensure you're using a supported URL format.

### "Failed to fetch manga data" error

Check your API key and internet connection.

## Support

For issues, please contact: {$this->getAuthor()}
MARKDOWN;
}
```

### Step 5: Add Helper Methods

```php
/**
 * Extract manga ID from URL
 */
protected function extractMangaId(string $url): ?string
{
    if (preg_match('/\/manga\/([a-z0-9\-]+)/i', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Extract chapter ID from URL
 */
protected function extractChapterId(string $url): ?string
{
    if (preg_match('/\/chapter\/([a-z0-9\-]+)/i', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Map external status to Mangoon status
 */
protected function mapStatus(string $status): string
{
    return match (strtolower($status)) {
        'ongoing', 'publishing' => 'ongoing',
        'completed', 'finished' => 'completed',
        'hiatus', 'on_hold' => 'hiatus',
        'cancelled', 'dropped' => 'cancelled',
        default => 'ongoing',
    };
}

/**
 * Create manga from metadata
 */
protected function createManga(array $metadata): ?Manga
{
    try {
        $manga = Manga::create([
            'slug' => $metadata['slug'],
            'author' => $metadata['author'] ?? null,
            'illustrator' => $metadata['illustrator'] ?? null,
            'publication_year' => $metadata['publication_year'] ?? null,
            'status' => $metadata['status'] ?? 'ongoing',
            'type' => $metadata['type'] ?? 'manga',
            'cover_image' => $metadata['cover_image'] ?? null,
            'is_active' => true,
        ]);
        
        // Create translation
        $manga->translations()->create([
            'language_code' => $this->config['preferred_language'] ?? 'en',
            'title' => $metadata['title'],
            'description' => $metadata['description'] ?? null,
        ]);
        
        return $manga;
        
    } catch (\Exception $e) {
        $this->handleError($e);
        return null;
    }
}
```

### Step 6: Register Your Plugin

In `bootstrap/providers.php` or via artisan command:

```php
use App\Models\Plugin;
use App\Plugins\MySourcePlugin;

// Register the plugin
Plugin::registerFromClass(MySourcePlugin::class);
```

Or via command line:

```bash
php artisan tinker

>>> App\Models\Plugin::registerFromClass(App\Plugins\MySourcePlugin::class);
```

## Plugin Interface Reference

### Required Methods

#### `getName(): string`
Returns the plugin display name.

#### `getVersion(): string`
Returns the plugin version (semantic versioning recommended).

#### `getDescription(): string`
Returns a brief description of what the plugin does.

#### `getAuthor(): string`
Returns the plugin author's name.

#### `getType(): string`
Returns the plugin type (e.g., 'downloader', 'parser', 'scraper').

#### `getConfigSchema(): array`
Returns JSON schema for configuration validation.

#### `getDefaultConfig(): array`
Returns default configuration values.

#### `getDocumentation(): string`
Returns plugin documentation in Markdown format.

#### `initialize(): void`
Called when plugin is loaded. Set up connections, validate config, etc.

#### `test(): array`
Test plugin functionality. Returns status array.

#### `download(string $url, array $options = []): array`
Main download method. Returns result array with manga.

#### `parseMetadata(string $url): array`
Parse manga metadata from URL.

#### `search(string $query, array $filters = []): array`
Search for manga.

#### `getChapters(string $url): array`
Get list of chapters for a manga.

#### `downloadChapter(string $chapterUrl, array $options = []): array`
Download a specific chapter.

### Optional Methods (Provided by AbstractPlugin)

- `setConfig(array $config): void`
- `getConfig(): array`
- `validateConfig(array $config): bool`
- `isReady(): bool`
- `handleError(\Exception $exception): void`
- `getStatistics(): array`
- `cleanup(): void`

## Configuration Schema

### JSON Schema Format

Your configuration schema should follow JSON Schema standards:

```php
public function getConfigSchema(): array
{
    return [
        'type' => 'object',
        'required' => ['api_key'], // Required fields
        'properties' => [
            'api_key' => [
                'type' => 'string',
                'minLength' => 10,
                'description' => 'API key for authentication',
            ],
            'rate_limit' => [
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => 100,
                'default' => 60,
                'description' => 'Requests per minute',
            ],
            'use_proxy' => [
                'type' => 'boolean',
                'default' => false,
                'description' => 'Use proxy for requests',
            ],
            'proxy_url' => [
                'type' => 'string',
                'format' => 'uri',
                'description' => 'Proxy server URL',
            ],
            'languages' => [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                ],
                'default' => ['en'],
                'description' => 'Supported languages',
            ],
        ],
    ];
}
```

### Supported Types

- `string`
- `integer`
- `number`
- `boolean`
- `array`
- `object`

### Validation

The `AbstractPlugin` class automatically validates configuration using the schema. Invalid configurations will throw an `InvalidArgumentException`.

## Testing Your Plugin

### Unit Testing

Create a test file `tests/Feature/MySourcePluginTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Plugin;
use App\Plugins\MySourcePlugin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MySourcePluginTest extends TestCase
{
    use RefreshDatabase;

    public function test_plugin_can_be_instantiated(): void
    {
        $plugin = new MySourcePlugin();
        
        $this->assertEquals('My Source Plugin', $plugin->getName());
        $this->assertEquals('1.0.0', $plugin->getVersion());
    }
    
    public function test_plugin_validates_config(): void
    {
        $plugin = new MySourcePlugin();
        
        $validConfig = [
            'api_key' => 'test-key-123',
            'timeout' => 30,
        ];
        
        $this->assertTrue($plugin->validateConfig($validConfig));
    }
    
    public function test_plugin_can_parse_url(): void
    {
        $plugin = new MySourcePlugin();
        
        $url = 'https://mysource.com/manga/test-manga';
        $metadata = $plugin->parseMetadata($url);
        
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('title', $metadata);
    }
}
```

### Manual Testing

```bash
# Register plugin
php artisan tinker
>>> App\Models\Plugin::registerFromClass(App\Plugins\MySourcePlugin::class);

# Install plugin
>>> $plugin = App\Models\Plugin::where('slug', 'my-source-plugin')->first();
>>> $plugin->install();

# Activate plugin
>>> $plugin->activate();

# Test plugin
>>> $plugin->test();

# Download manga
>>> $manager = app(App\Services\PluginManager::class);
>>> $result = $manager->downloadManga('https://mysource.com/manga/test');
>>> $result['success']; // Should be true
```

## Best Practices

### 1. Error Handling

Always wrap external calls in try-catch blocks:

```php
try {
    $response = Http::get($url);
    if (!$response->successful()) {
        throw new \RuntimeException('Request failed');
    }
    return $response->json();
} catch (\Exception $e) {
    $this->handleError($e);
    return [];
}
```

### 2. Rate Limiting

Respect the source's rate limits:

```php
protected ?int $rateLimit = 60; // 60 requests per minute
protected int $concurrentLimit = 1; // 1 concurrent request
```

### 3. Logging

Use the built-in logging:

```php
$this->log('info', 'Starting download');
$this->log('error', 'Download failed', ['url' => $url]);
```

### 4. Statistics

Update statistics for monitoring:

```php
$this->updateStatistics(
    success: true,
    chaptersDownloaded: 10,
    pagesDownloaded: 250
);
```

### 5. Configuration

Always provide sensible defaults:

```php
public function getDefaultConfig(): array
{
    return [
        'timeout' => 30,
        'retry_count' => 3,
        'user_agent' => 'Mangoon/1.0',
    ];
}
```

### 6. URL Validation

Validate URLs before processing:

```php
protected function isValidUrl(string $url): bool
{
    return preg_match('/^https?:\/\/mysource\.com\/manga\/[a-z0-9\-]+$/i', $url);
}
```

### 7. Data Transformation

Always transform external data to Mangoon format:

```php
protected function transformToMangoonFormat(array $externalData): array
{
    return [
        'title' => $externalData['name'] ?? 'Unknown',
        'slug' => \Str::slug($externalData['name'] ?? 'unknown'),
        'author' => $externalData['creator'] ?? null,
        // ... more mappings
    ];
}
```

### 8. Documentation

Provide comprehensive documentation:

- Installation steps
- Configuration options
- Usage examples
- Troubleshooting tips
- Supported URL formats

## Advanced Topics

### Authentication

If your source requires authentication:

```php
protected function authenticate(): string
{
    $response = Http::post("{$this->baseUrl}/api/auth", [
        'api_key' => $this->config['api_key'],
    ]);
    
    return $response->json('token');
}

public function download(string $url, array $options = []): array
{
    $token = $this->authenticate();
    
    $response = Http::withToken($token)
        ->get($url);
    
    // ... process response
}
```

### Caching

Implement caching for better performance:

```php
use Illuminate\Support\Facades\Cache;

protected function getCachedMetadata(string $mangaId): ?array
{
    $cacheKey = "manga_metadata_{$mangaId}";
    
    return Cache::remember($cacheKey, 3600, function () use ($mangaId) {
        return $this->fetchMetadata($mangaId);
    });
}
```

### Image Processing

Download and process images:

```php
use Illuminate\Support\Facades\Storage;

protected function downloadImage(string $url, string $path): bool
{
    $response = Http::get($url);
    
    if ($response->successful()) {
        Storage::disk('public')->put($path, $response->body());
        return true;
    }
    
    return false;
}
```

### Queue Support

Use queues for long-running downloads:

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\DownloadMangaChapter;

public function downloadChapter(string $chapterUrl, array $options = []): array
{
    $jobId = Queue::push(new DownloadMangaChapter($chapterUrl, $options));
    
    return $this->successResponse('Chapter download queued', [
        'job_id' => $jobId,
    ]);
}
```

### Proxy Support

Add proxy support for restricted sources:

```php
protected function makeRequest(string $url): \Illuminate\Http\Client\Response
{
    $client = Http::timeout($this->config['timeout'] ?? 30);
    
    if ($this->config['use_proxy'] ?? false) {
        $client = $client->withOptions([
            'proxy' => $this->config['proxy_url'],
        ]);
    }
    
    return $client->get($url);
}
```

## Example Plugins

### Simple HTTP Plugin

```php
<?php

namespace App\Plugins;

class SimpleHttpPlugin extends AbstractPlugin
{
    protected string $name = 'Simple HTTP Plugin';
    protected string $version = '1.0.0';
    protected string $description = 'Basic HTTP manga downloader';
    
    public function download(string $url, array $options = []): array
    {
        $response = Http::get($url);
        $html = $response->body();
        
        // Parse HTML and extract manga data
        // Create manga
        // Return result
    }
    
    public function parseMetadata(string $url): array
    {
        // Parse metadata from HTML
    }
    
    // ... other methods
}
```

### API-Based Plugin

```php
<?php

namespace App\Plugins;

class ApiBasedPlugin extends AbstractPlugin
{
    protected string $name = 'API Based Plugin';
    protected string $apiBaseUrl = 'https://api.source.com/v1';
    
    protected function callApi(string $endpoint, array $params = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['api_key'],
        ])->get("{$this->apiBaseUrl}/{$endpoint}", $params);
        
        return $response->json();
    }
    
    public function search(string $query, array $filters = []): array
    {
        return $this->callApi('search', ['q' => $query]);
    }
}
```

## Troubleshooting

### Common Issues

#### Plugin Not Showing Up

**Solution**: Ensure the plugin is registered:
```php
Plugin::registerFromClass(YourPlugin::class);
```

#### Configuration Validation Fails

**Solution**: Check your config schema and ensure all required fields are provided.

#### Download Fails

**Solution**: 
- Check error logs
- Verify URL format
- Test API endpoint manually
- Check rate limits

#### Plugin Won't Activate

**Solution**:
- Ensure plugin is installed first
- Check requirements (PHP version, dependencies)
- Review error logs

### Debugging

Enable debug logging:

```php
protected function debug(string $message, array $context = []): void
{
    if ($this->config['debug'] ?? false) {
        $this->log('debug', $message, $context);
    }
}
```

Use tinker for interactive debugging:

```bash
php artisan tinker
>>> $plugin = new App\Plugins\MyPlugin();
>>> $plugin->test();
>>> $plugin->download('https://...');
```

## API Reference

### Response Format

#### Success Response
```php
[
    'success' => true,
    'message' => 'Operation completed',
    'data' => [...],
    'manga' => Manga|null,
]
```

#### Error Response
```php
[
    'success' => false,
    'message' => 'Error description',
    'data' => [],
    'manga' => null,
]
```

### Metadata Format

```php
[
    'external_id' => 'string',
    'title' => 'string',
    'slug' => 'string',
    'author' => 'string|null',
    'illustrator' => 'string|null',
    'description' => 'string|null',
    'cover_image' => 'string|null',
    'status' => 'ongoing|completed|hiatus|cancelled',
    'type' => 'manga|manhwa|manhua|webtoon',
    'publication_year' => 'int|null',
    'tags' => 'array',
]
```

### Chapter Format

```php
[
    'id' => 'string',
    'chapter' => 'string',
    'title' => 'string|null',
    'volume' => 'string|null',
    'language' => 'string',
    'pages' => 'int',
    'url' => 'string',
]
```

## Contributing

To contribute your plugin to Mangoon:

1. Ensure code quality with `vendor/bin/pint`
2. Write comprehensive tests
3. Document all configuration options
4. Provide usage examples
5. Submit a pull request

## License

All plugins should be compatible with Mangoon's MIT license.

---

**Happy Plugin Development!**

For support and questions, visit: https://github.com/mangoon/mangoon