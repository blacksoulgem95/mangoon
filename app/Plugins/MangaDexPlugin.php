<?php

namespace App\Plugins;

use App\Models\Manga;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * MangaDex Plugin
 *
 * This plugin allows downloading manga from MangaDex (mangadex.org).
 * It supports metadata parsing, chapter listing, and chapter downloads.
 *
 * @author Mangoon Team
 *
 * @version 1.0.0
 */
class MangaDexPlugin extends AbstractPlugin
{
    /**
     * Plugin name.
     */
    protected string $name = 'MangaDex Plugin';

    /**
     * Plugin version.
     */
    protected string $version = '1.0.0';

    /**
     * Plugin description.
     */
    protected string $description = 'Download manga from MangaDex - the largest open-source manga database';

    /**
     * Plugin author.
     */
    protected string $author = 'Mangoon Team';

    /**
     * Plugin author URL.
     */
    protected ?string $authorUrl = 'https://github.com/mangoon';

    /**
     * Plugin icon path or URL.
     */
    protected ?string $icon = '/images/plugins/mangadex.png';

    /**
     * Plugin type.
     */
    protected string $type = 'downloader';

    /**
     * Rate limit (requests per minute).
     */
    protected ?int $rateLimit = 60;

    /**
     * Maximum concurrent downloads.
     */
    protected int $concurrentLimit = 3;

    /**
     * Plugin priority.
     */
    protected int $priority = 100;

    /**
     * MangaDex API base URL.
     */
    protected string $apiBaseUrl = 'https://api.mangadex.org';

    /**
     * MangaDex API version.
     */
    protected string $apiVersion = 'v5';

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
                'api_base_url' => [
                    'type' => 'string',
                    'default' => 'https://api.mangadex.org',
                    'description' => 'MangaDex API base URL',
                ],
                'api_version' => [
                    'type' => 'string',
                    'default' => 'v5',
                    'description' => 'MangaDex API version',
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
                'preferred_language' => [
                    'type' => 'string',
                    'default' => 'en',
                    'description' => 'Preferred language for manga downloads',
                ],
                'include_chapters' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Include chapter data when downloading manga',
                ],
                'download_quality' => [
                    'type' => 'string',
                    'default' => 'data',
                    'description' => 'Image quality: data (original) or data-saver (compressed)',
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
            'api_base_url' => 'https://api.mangadex.org',
            'api_version' => 'v5',
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (compatible; Mangoon/1.0)',
            'preferred_language' => 'en',
            'include_chapters' => true,
            'download_quality' => 'data',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentation(): string
    {
        return <<<MARKDOWN
# MangaDex Plugin

Version: {$this->getVersion()}
Author: {$this->getAuthor()}

## Description

The MangaDex Plugin allows you to download manga from MangaDex (mangadex.org),
the world's largest open-source manga database. This plugin supports:

- Metadata parsing (title, author, tags, description, etc.)
- Chapter listing with multiple language support
- Chapter downloading with quality options
- Search functionality
- Multi-language support

## Installation

1. Ensure you have PHP 8.3 or higher installed
2. Ensure you have Laravel 12.0 or higher installed
3. The plugin is included by default in Mangoon
4. Activate the plugin through the admin panel

## Configuration

### Basic Settings

- **Enabled**: Enable or disable the plugin
- **API Base URL**: The MangaDex API base URL (default: https://api.mangadex.org)
- **API Version**: MangaDex API version (default: v5)
- **Timeout**: Request timeout in seconds (default: 30)

### Download Settings

- **Preferred Language**: Default language for manga downloads (default: en)
- **Include Chapters**: Automatically fetch chapter data (default: true)
- **Download Quality**: Image quality - 'data' for original or 'data-saver' for compressed (default: data)

## Usage

### Downloading a Manga

1. Copy the manga URL from MangaDex (e.g., https://mangadex.org/title/xxx)
2. Go to the Manga Management section in the admin panel
3. Click "Download from Source"
4. Paste the URL and click "Download"
5. The plugin will automatically parse metadata and create the manga entry

### Searching for Manga

Use the search functionality to find manga directly from MangaDex:

```php
\$plugin = new MangaDexPlugin();
\$results = \$plugin->search('One Piece');
```

### Getting Chapters

```php
\$plugin = new MangaDexPlugin();
\$chapters = \$plugin->getChapters('https://mangadex.org/title/xxx');
```

### Downloading a Chapter

```php
\$plugin = new MangaDexPlugin();
\$result = \$plugin->downloadChapter('https://mangadex.org/chapter/xxx');
```

## URL Formats Supported

- `https://mangadex.org/title/{manga-id}`
- `https://mangadex.org/title/{manga-id}/{manga-title}`
- `https://mangadex.org/chapter/{chapter-id}`

## Rate Limiting

The plugin respects MangaDex's rate limits:
- Maximum 60 requests per minute
- Maximum 3 concurrent downloads

## Troubleshooting

### "Connection timeout" error

Increase the timeout value in the plugin configuration.

### "Rate limit exceeded" error

The plugin has exceeded MangaDex's rate limits. Wait a few minutes before retrying.

### "Invalid URL format" error

Ensure you're using a valid MangaDex URL format (see above).

### "Manga not found" error

The manga ID in the URL doesn't exist or has been removed from MangaDex.

## API Documentation

For more information about the MangaDex API, visit:
https://api.mangadex.org/docs/

## Support

For support, please visit: https://github.com/mangoon/mangoon/issues

## License

This plugin is open-source and available under the same license as Mangoon.
MARKDOWN;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        parent::initialize();

        // Override API URLs if configured
        if (isset($this->config['api_base_url'])) {
            $this->apiBaseUrl = $this->config['api_base_url'];
        }

        if (isset($this->config['api_version'])) {
            $this->apiVersion = $this->config['api_version'];
        }

        $this->log('info', 'MangaDex plugin initialized', [
            'api_base_url' => $this->apiBaseUrl,
            'api_version' => $this->apiVersion,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function test(): array
    {
        try {
            $this->initialize();

            // Test API connection
            $response = Http::timeout($this->config['timeout'] ?? 30)
                ->withHeaders(['User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0'])
                ->get("{$this->apiBaseUrl}/ping");

            if ($response->successful()) {
                return $this->successResponse('MangaDex API connection successful', [
                    'api_status' => 'online',
                    'response_time' => $response->handlerStats()['total_time'] ?? null,
                ]);
            }

            return $this->errorResponse('MangaDex API connection failed', [
                'status_code' => $response->status(),
            ]);
        } catch (\Exception $e) {
            $this->handleError($e);

            return $this->errorResponse('Plugin test failed: '.$e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $url, array $options = []): array
    {
        try {
            $this->initialize();

            $mangaId = $this->extractMangaId($url);
            if (! $mangaId) {
                return $this->errorResponse('Invalid MangaDex URL format');
            }

            $metadata = $this->parseMetadata($url);
            if (empty($metadata)) {
                return $this->errorResponse('Failed to parse manga metadata');
            }

            // Create manga entry
            $manga = $this->createMangaFromMetadata($metadata, $options);

            if (! $manga) {
                return $this->errorResponse('Failed to create manga entry');
            }

            $this->updateStatistics(true, 0, 0);
            $this->log('info', "Successfully downloaded manga: {$manga->slug}");

            return $this->successResponse('Manga downloaded successfully', [
                'manga_id' => $manga->id,
                'slug' => $manga->slug,
            ], $manga);
        } catch (\Exception $e) {
            $this->handleError($e);
            $this->updateStatistics(false);

            return $this->errorResponse('Download failed: '.$e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseMetadata(string $url): array
    {
        try {
            $mangaId = $this->extractMangaId($url);
            if (! $mangaId) {
                throw new \InvalidArgumentException('Invalid MangaDex URL format');
            }

            $response = Http::timeout($this->config['timeout'] ?? 30)
                ->withHeaders(['User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0'])
                ->get("{$this->apiBaseUrl}/manga/{$mangaId}", [
                    'includes[]' => ['author', 'artist', 'cover_art'],
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Failed to fetch manga data from MangaDex API');
            }

            $data = $response->json('data');
            if (! $data) {
                throw new \RuntimeException('Invalid response from MangaDex API');
            }

            return $this->transformMangaDexData($data);
        } catch (\Exception $e) {
            $this->handleError($e);
            $this->log('error', "Failed to parse metadata: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $query, array $filters = []): array
    {
        try {
            $this->initialize();

            $params = array_merge([
                'title' => $query,
                'limit' => $filters['limit'] ?? 20,
                'offset' => $filters['offset'] ?? 0,
                'includes[]' => ['cover_art'],
            ], $filters);

            $response = Http::timeout($this->config['timeout'] ?? 30)
                ->withHeaders(['User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0'])
                ->get("{$this->apiBaseUrl}/manga", $params);

            if (! $response->successful()) {
                return [];
            }

            $results = $response->json('data', []);

            return array_map(function ($item) {
                return $this->transformMangaDexData($item);
            }, $results);
        } catch (\Exception $e) {
            $this->handleError($e);
            $this->log('error', "Search failed: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChapters(string $url): array
    {
        try {
            $this->initialize();

            $mangaId = $this->extractMangaId($url);
            if (! $mangaId) {
                throw new \InvalidArgumentException('Invalid MangaDex URL format');
            }

            $chapters = [];
            $offset = 0;
            $limit = 100;

            do {
                $response = Http::timeout($this->config['timeout'] ?? 30)
                    ->withHeaders(['User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0'])
                    ->get("{$this->apiBaseUrl}/manga/{$mangaId}/feed", [
                        'limit' => $limit,
                        'offset' => $offset,
                        'translatedLanguage[]' => [$this->config['preferred_language'] ?? 'en'],
                        'order[chapter]' => 'asc',
                    ]);

                if (! $response->successful()) {
                    break;
                }

                $data = $response->json('data', []);
                if (empty($data)) {
                    break;
                }

                foreach ($data as $chapter) {
                    $chapters[] = $this->transformChapterData($chapter);
                }

                $offset += $limit;
            } while (count($data) === $limit);

            return $chapters;
        } catch (\Exception $e) {
            $this->handleError($e);
            $this->log('error', "Failed to get chapters: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function downloadChapter(string $chapterUrl, array $options = []): array
    {
        try {
            $this->initialize();

            $chapterId = $this->extractChapterId($chapterUrl);
            if (! $chapterId) {
                return $this->errorResponse('Invalid chapter URL format');
            }

            // Get chapter info
            $chapterResponse = Http::timeout($this->config['timeout'] ?? 30)
                ->withHeaders(['User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0'])
                ->get("{$this->apiBaseUrl}/chapter/{$chapterId}");

            if (! $chapterResponse->successful()) {
                return $this->errorResponse('Failed to fetch chapter data');
            }

            // Get chapter pages
            $atHomeResponse = Http::timeout($this->config['timeout'] ?? 30)
                ->withHeaders(['User-Agent' => $this->config['user_agent'] ?? 'Mangoon/1.0'])
                ->get("{$this->apiBaseUrl}/at-home/server/{$chapterId}");

            if (! $atHomeResponse->successful()) {
                return $this->errorResponse('Failed to fetch chapter pages');
            }

            $chapterData = $chapterResponse->json('data');
            $atHomeData = $atHomeResponse->json();

            $baseUrl = $atHomeData['baseUrl'] ?? '';
            $hash = $atHomeData['chapter']['hash'] ?? '';
            $quality = $this->config['download_quality'] ?? 'data';
            $pages = $atHomeData['chapter'][$quality] ?? [];

            $pageUrls = array_map(function ($page) use ($baseUrl, $quality, $hash) {
                return "{$baseUrl}/{$quality}/{$hash}/{$page}";
            }, $pages);

            $this->updateStatistics(true, 1, count($pageUrls));

            return $this->successResponse('Chapter downloaded successfully', [
                'chapter_id' => $chapterId,
                'pages' => $pageUrls,
                'page_count' => count($pageUrls),
                'chapter_data' => $this->transformChapterData($chapterData),
            ]);
        } catch (\Exception $e) {
            $this->handleError($e);
            $this->updateStatistics(false);

            return $this->errorResponse('Chapter download failed: '.$e->getMessage());
        }
    }

    /**
     * Extract manga ID from URL.
     */
    protected function extractMangaId(string $url): ?string
    {
        if (preg_match('/\/title\/([a-f0-9\-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract chapter ID from URL.
     */
    protected function extractChapterId(string $url): ?string
    {
        if (preg_match('/\/chapter\/([a-f0-9\-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Transform MangaDex data to Mangoon format.
     */
    protected function transformMangaDexData(array $data): array
    {
        $attributes = $data['attributes'] ?? [];
        $relationships = $data['relationships'] ?? [];

        $title = $attributes['title']['en'] ?? array_values($attributes['title'])[0] ?? 'Unknown';
        $description = $attributes['description']['en'] ?? array_values($attributes['description'])[0] ?? null;

        $author = null;
        $illustrator = null;
        $coverImage = null;

        foreach ($relationships as $relation) {
            if ($relation['type'] === 'author') {
                $author = $relation['attributes']['name'] ?? null;
            } elseif ($relation['type'] === 'artist') {
                $illustrator = $relation['attributes']['name'] ?? null;
            } elseif ($relation['type'] === 'cover_art') {
                $coverImage = $relation['attributes']['fileName'] ?? null;
            }
        }

        return [
            'external_id' => $data['id'],
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $description,
            'author' => $author,
            'illustrator' => $illustrator,
            'cover_image' => $coverImage,
            'status' => $this->mapStatus($attributes['status'] ?? 'ongoing'),
            'type' => 'manga',
            'original_language' => $attributes['originalLanguage'] ?? null,
            'publication_year' => $attributes['year'] ?? null,
            'tags' => array_map(function ($tag) {
                return $tag['attributes']['name']['en'] ?? null;
            }, $attributes['tags'] ?? []),
            'alternative_titles' => $attributes['altTitles'] ?? [],
        ];
    }

    /**
     * Transform MangaDex chapter data.
     */
    protected function transformChapterData(array $data): array
    {
        $attributes = $data['attributes'] ?? [];

        return [
            'id' => $data['id'],
            'chapter' => $attributes['chapter'] ?? null,
            'title' => $attributes['title'] ?? null,
            'volume' => $attributes['volume'] ?? null,
            'language' => $attributes['translatedLanguage'] ?? null,
            'pages' => $attributes['pages'] ?? 0,
            'published_at' => $attributes['publishAt'] ?? null,
        ];
    }

    /**
     * Map MangaDex status to Mangoon status.
     */
    protected function mapStatus(string $status): string
    {
        return match ($status) {
            'ongoing' => 'ongoing',
            'completed' => 'completed',
            'hiatus' => 'hiatus',
            'cancelled' => 'cancelled',
            default => 'ongoing',
        };
    }

    /**
     * Create manga from metadata.
     */
    protected function createMangaFromMetadata(array $metadata, array $options = []): ?Manga
    {
        try {
            $manga = Manga::create([
                'slug' => $metadata['slug'],
                'author' => $metadata['author'],
                'illustrator' => $metadata['illustrator'],
                'publication_year' => $metadata['publication_year'],
                'original_language' => $metadata['original_language'],
                'status' => $metadata['status'],
                'type' => $metadata['type'],
                'cover_image' => $metadata['cover_image'],
                'is_active' => true,
            ]);

            // Create default translation
            $manga->translations()->create([
                'language_code' => $this->config['preferred_language'] ?? 'en',
                'title' => $metadata['title'],
                'description' => $metadata['description'],
            ]);

            return $manga;
        } catch (\Exception $e) {
            $this->handleError($e);

            return null;
        }
    }
}
