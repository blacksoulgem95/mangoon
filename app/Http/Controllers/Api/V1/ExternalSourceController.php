<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadChapters;
use App\Models\Manga;
use App\Models\ExternalSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ExternalSourceController extends Controller
{
    public function search(string $source, Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return response()->json(['error' => 'Search query required'], 400);
        }

        try {
            $response = match ($source) {
                'nhentai' => $this->searchNhentai($query),
                'mangadex' => $this->searchMangaDex($query),
                default => response()->json(['error' => 'Source not supported'], 400),
            };

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDetails(string $source, string $sourceId)
    {
        try {
            $details = match ($source) {
                'nhentai' => $this->getNhentaiDetails($sourceId),
                'mangadex' => $this->getMangaDexDetails($sourceId),
                default => response()->json(['error' => 'Source not supported'], 400),
            };

            return response()->json($details);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch details',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function download(string $source, string $sourceId, Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'library_id' => 'nullable|exists:libraries,id',
            'tags' => 'nullable|array',
        ]);

        try {
            $details = match ($source) {
                'nhentai' => $this->getNhentaiDetails($sourceId),
                'mangadex' => $this->getMangaDexDetails($sourceId),
                default => throw new \Exception('Source not supported'),
            };

            $manga = Manga::create([
                'title' => $validated['title'],
                'is_mature' => $details['is_mature'] ?? false,
                'library_id' => $validated['library_id'] ?? null,
            ]);

            ExternalSource::create([
                'manga_id' => $manga->id,
                'source_name' => $source,
                'source_id' => $sourceId,
                'metadata' => $details,
            ]);

            $chapterUrls = $details['chapter_urls'] ?? [];
            
            if (!empty($chapterUrls)) {
                DownloadChapters::dispatch(
                    $manga,
                    $chapterUrls,
                    $source,
                    Auth::id()
                );
            }

            return response()->json([
                'message' => 'Download started',
                'manga' => $manga,
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Download failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function searchNhentai(string $query): array
    {
        $response = Http::get('https://nhentai.net/api/search', [
            'q' => $query,
            'page' => 1,
            'sort' => 'popular',
        ]);

        if (!$response->successful()) {
            throw new \Exception('nhentai API request failed');
        }

        $data = $response->json();
        
        return [
            'results' => collect($data['result'] ?? [])->map(function ($item) {
                return [
                    'source_id' => $item['id'],
                    'title' => $item['title']['english'] ?? $item['title']['native'],
                    'artist' => $this->getArtist($item['tags']),
                    'author' => $this->getAuthor($item['tags']),
                    'tags' => $this->getTags($item['tags']),
                    'thumbnail' => "https://i.nhentai.net/galleries/{$item['id']}/t.jpg",
                    'is_mature' => true,
                ];
            })->toArray(),
            'total' => $data['total'] ?? 0,
        ];
    }

    protected function getNhentaiDetails(string $sourceId): array
    {
        $response = Http::get("https://nhentai.net/api/galleries/{$sourceId}");

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch nhentai details');
        }

        $data = $response->json();
        
        $pages = collect($data['images']['pages'] ?? [])->map(function ($page, $index) use ($sourceId) {
            $ext = $page['t'] ?? 'jpg';
            $pageNumber = $index + 1;
            return "https://i.nhentai.net/galleries/{$sourceId}/{$pageNumber}.{$ext}";
        })->toArray();

        return [
            'source_id' => $sourceId,
            'title' => $data['title']['english'] ?? $data['title']['native'],
            'artist' => $this->getArtist($data['tags']),
            'author' => $this->getAuthor($data['tags']),
            'tags' => $this->getTags($data['tags']),
            'is_mature' => true,
            'page_urls' => $pages,
            'chapter_urls' => [$this->buildCbzUrl($sourceId)],
        ];
    }

    protected function searchMangaDex(string $query): array
    {
        $response = Http::get('https://api.mangadex.org/manga', [
            'title' => $query,
            'includes[]' => 'cover_art',
            'limit' => 20,
        ]);

        if (!$response->successful()) {
            throw new \Exception('MangaDex API request failed');
        }

        $data = $response->json();
        
        return [
            'results' => collect($data['data'] ?? [])->map(function ($item) {
                $attributes = $item['attributes'];
                return [
                    'source_id' => $item['id'],
                    'title' => $attributes['title']['en'] ?? reset($attributes['title']),
                    'artist' => $attributes['artist'] ?? 'Unknown',
                    'author' => $attributes['author'] ?? 'Unknown',
                    'tags' => collect($attributes['tags'] ?? [])->map(fn($t) => $t['attributes']['name']['en'])->toArray(),
                    'thumbnail' => null,
                    'is_mature' => in_array('mature', $attributes['contentRating'] ?? []),
                ];
            })->toArray(),
            'total' => $data['total'] ?? 0,
        ];
    }

    protected function getMangaDexDetails(string $sourceId): array
    {
        $response = Http::get("https://api.mangadex.org/manga/{$sourceId}", [
            'includes[]' => 'cover_art',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch MangaDex details');
        }

        $data = $response->json()['data'];
        $attributes = $data['attributes'];

        return [
            'source_id' => $sourceId,
            'title' => $attributes['title']['en'] ?? reset($attributes['title']),
            'artist' => $attributes['artist'] ?? 'Unknown',
            'author' => $attributes['author'] ?? 'Unknown',
            'tags' => collect($attributes['tags'] ??())->map(fn($t) => $t['attributes']['name']['en'])->toArray(),
            'is_mature' => in_array('mature', $attributes['contentRating'] ?? []),
            'chapter_urls' => [],
        ];
    }

    protected function getArtist(array $tags): string
    {
        $artist = collect($tags)->first(fn($t) => $t['type'] === 'artist');
        return $artist['value'] ?? 'Unknown';
    }

    protected function getAuthor(array $tags): string
    {
        $author = collect($tags)->first(fn($t) => $t['type'] === 'author');
        return $author['value'] ?? 'Unknown';
    }

    protected function getTags(array $tags): array
    {
        return collect($tags)
            ->filter(fn($t) => in_array($t['type'], ['tag', 'parody', 'character']))
            ->map(fn($t) => $t['value'])
            ->toArray();
    }

    protected function buildCbzUrl(string $sourceId): string
    {
        return "https://nhentai.net/galleria/{$sourceId}/";
    }
}
