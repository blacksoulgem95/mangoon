<?php

namespace App\Jobs;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\UserCookie;
use App\Services\CbzProcessor;
use App\Services\S3Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DownloadChapters implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(
        protected Manga $manga,
        protected array $chapterUrls,
        protected string $source,
        protected int $userId
    ) {}

    public function handle(CbzProcessor $cbzProcessor, S3Service $s3Service): void
    {
        $cookies = $this->getCookies();
        
        foreach ($this->chapterUrls as $chapterUrl) {
            try {
                $this->downloadChapter($chapterUrl, $cookies, $cbzProcessor, $s3Service);
            } catch (\Exception $e) {
                Log::error('Failed to download chapter', [
                    'manga_id' => $this->manga->id,
                    'chapter_url' => $chapterUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function getCookies(): array
    {
        $cookie = UserCookie::where('user_id', $this->userId)
            ->where('source', $this->source)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        return $cookie ? $cookie->cookies : [];
    }

    protected function downloadChapter(string $chapterUrl, array $cookies, CbzProcessor $cbzProcessor, S3Service $s3Service): void
    {
        $response = Http::withCookies($cookies)->get($chapterUrl);
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch chapter: {$response->status()}");
        }

        $pages = $this->extractPages($response->body());
        $chapter = $this->createChapter($pages);
        
        $cbzPath = temp_path("/chapter_{$chapter->id}.cbz");
        $this->createCbz($pages, $cbzPath);
        
        $s3Path = $s3Service->getCbzPath($this->manga->id, (string)$chapter->id);
        $s3Service->uploadStream($s3Path, fopen($cbzPath, 'r'));
        
        unlink($cbzPath);
    }

    protected function extractPages(string $html): array
    {
        // Parse HTML to extract image URLs - implementation depends on source
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        $images = [];
        $imageTags = $xpath->query('//img');
        
        foreach ($imageTags as $tag) {
            $src = $tag->getAttribute('src');
            if ($src) {
                $images[] = $src;
            }
        }

        return $images;
    }

    protected function createChapter(array $pages): Chapter
    {
        return Chapter::create([
            'manga_id' => $this->manga->id,
            'chapter_number' => (string)($this->manga->chapters()->count() + 1),
            'title' => 'Chapter ' . ($this->manga->chapters()->count() + 1),
            'page_count' => count($pages),
        ]);
    }

    protected function createCbz(array $pages, string $path): void
    {
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE);
        
        foreach ($pages as $index => $imageUrl) {
            $imageContent = file_get_contents($imageUrl);
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = str_pad($index + 1, 4, '0', STR_PAD_LEFT) . '.' . $extension;
            $zip->addFromString($filename, $imageContent);
        }
        
        $zip->close();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Download chapters job failed', [
            'manga_id' => $this->manga->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
