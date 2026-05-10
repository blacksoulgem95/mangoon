<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    protected string $disk = 'manga';

    public function __construct()
    {
        if (config('filesystems.manga_disk')) {
            $this->disk = config('filesystems.manga_disk');
        }
    }

    public function upload(string $path, string $contents, string $disk = null): bool
    {
        return Storage::disk($disk ?? $this->disk)->put($path, $contents);
    }

    public function uploadStream(string $path, $resource, string $disk = null): bool
    {
        return Storage::disk($disk ?? $this->disk)->writeStream($path, $resource);
    }

    public function get(string $path, string $disk = null): ?string
    {
        return Storage::disk($disk ?? $this->disk)->get($path);
    }

    public function getStream(string $path, string $disk = null)
    {
        return Storage::disk($disk ?? $this->disk)->readStream($path);
    }

    public function delete(string $path, string $disk = null): bool
    {
        return Storage::disk($disk ?? $this->disk)->delete($path);
    }

    public function exists(string $path, string $disk = null): bool
    {
        return Storage::disk($disk ?? $this->disk)->exists($path);
    }

    public function url(string $path, string $disk = null): string
    {
        return Storage::disk($disk ?? $this->disk)->url($path);
    }

    public function temporaryUrl(string $path, int $expirationMinutes = 60, string $disk = null): string
    {
        return Storage::disk($disk ?? $this->disk)->temporaryUrl(
            $path,
            now()->addMinutes($expirationMinutes),
            [
                'ResponseContentDisposition' => 'inline',
            ]
        );
    }

    public function getPreSignedUrl(string $path, int $expirationMinutes = 60, string $disk = null): string
    {
        $driver = Storage::disk($disk ?? $this->disk);
        
        if (method_exists($driver, 'getAdapter')) {
            $adapter = $driver->getAdapter();
            
            if (method_exists($adapter, 'getClient')) {
                $client = $adapter->getClient();
                $bucket = config("filesystems.disks.{$disk ?? $this->disk}.bucket");
                
                $command = $client->getCommand('GetObject', [
                    'Bucket' => $bucket,
                    'Key' => $path,
                    'ResponseContentDisposition' => 'inline',
                ]);
                
                $request = $client->createRequest('GetObject', $command);
                $signedUrl = $client->getCommand('GetObject', [
                    'Bucket' => $bucket,
                    'Key' => $path,
                ])->getHandlerList()->invoke(
                    $client->getCommand('GetObject', [
                        'Bucket' => $bucket,
                        'Key' => $path,
                    ])
                );
                
                return $client->createPresignedRequest(
                    $command,
                    "+{$expirationMinutes} minutes"
                )->getUri()->__toString();
            }
        }
        
        return $this->temporaryUrl($path, $expirationMinutes, $disk);
    }

    public function listFiles(string $prefix, string $disk = null): array
    {
        return Storage::disk($disk ?? $this->disk)->allFiles($prefix);
    }

    public function createDirectory(string $path, string $disk = null): bool
    {
        return Storage::disk($disk ?? $this->disk)->makeDirectory($path);
    }

    public function buildPath(string $mangaId, string $chapterId = null, string $page = null, string $disk = null): string
    {
        $parts = ["mangas/{$mangaId}"];
        
        if ($chapterId) {
            $parts[] = "chapters/{$chapterId}";
            
            if ($page !== null) {
                $parts[] = "pages/{$page}.webp";
            }
        }
        
        return implode('/', $parts);
    }

    public function getCbzPath(string $mangaId, string $chapterId, string $disk = null): string
    {
        return "mangas/{$mangaId}/chapters/{$chapterId}/chapter.cbz";
    }

    public function getCbzUrl(string $mangaId, string $chapterId, int $expirationMinutes = 60, string $disk = null): string
    {
        $path = $this->getCbzPath($mangaId, $chapterId, $disk);
        return $this->getPreSignedUrl($path, $expirationMinutes, $disk);
    }

    public function getPageUrl(string $mangaId, string $chapterId, int $page, int $expirationMinutes = 60, string $disk = null): string
    {
        $path = $this->buildPath($mangaId, $chapterId, (string)$page, $disk);
        return $this->getPreSignedUrl($path, $expirationMinutes, $disk);
    }
}
