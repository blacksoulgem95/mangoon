<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CbzProcessor
{
    protected S3Service $s3Service;
    protected string $tempPath = '/tmp/opencode';

    public function __construct(S3Service $s3Service)
    {
        $this->s3Service = $s3Service;
    }

    public function extractToS3(string $mangaId, string $chapterId, string $cbzPath, string $disk = null): array
    {
        $pages = [];
        $pageCount = 0;

        $zip = new ZipArchive();
        $res = $zip->open($cbzPath);

        if ($res === true) {
            $images = [];
            
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $images[$i] = $filename;
                }
            }

            asort($images);
            
            foreach ($images as $index => $filename) {
                $pageCount++;
                $sourceContent = $zip->getFromName($filename);
                
                if ($sourceContent !== false) {
                    $webpContent = $this->convertToWebP($sourceContent, $filename);
                    $s3Path = $this->s3Service->buildPath($mangaId, $chapterId, (string)$pageCount, $disk);
                    
                    if ($this->s3Service->upload($s3Path, $webpContent, $disk)) {
                        $pages[] = [
                            'number' => $pageCount,
                            'original_filename' => $filename,
                            's3_path' => $s3Path,
                        ];
                    }
                }
            }

            $zip->close();
        }

        return [
            'pages' => $pages,
            'page_count' => $pageCount,
        ];
    }

    public function extractOnDemand(string $mangaId, string $chapterId, string $disk = null): array
    {
        $cbzPath = $this->s3Service->getCbzPath($mangaId, $chapterId, $disk);
        
        if (!$this->s3Service->exists($cbzPath, $disk)) {
            return ['error' => 'CBZ file not found'];
        }

        $tempFile = $this->tempPath . "/cbz_{$mangaId}_{$chapterId}.cbz";
        
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }

        $stream = $this->s3Service->getStream($cbzPath, $disk);
        $tempStream = fopen($tempFile, 'w');
        
        if ($stream && $tempStream) {
            stream_copy_to_stream($stream, $tempStream);
            fclose($tempStream);
            fclose($stream);

            $result = $this->extractToS3($mangaId, $chapterId, $tempFile, $disk);
            
            unlink($tempFile);
            
            return $result;
        }

        return ['error' => 'Failed to download CBZ file'];
    }

    public function getPage(string $mangaId, string $chapterId, int $page, string $disk = null): ?string
    {
        $s3Path = $this->s3Service->buildPath($mangaId, $chapterId, (string)$page, $disk);
        
        if (!$this->s3Service->exists($s3Path, $disk)) {
            $result = $this->extractOnDemand($mangaId, $chapterId, $disk);
            
            if (isset($result['error'])) {
                return null;
            }
        }

        return $this->s3Service->get($s3Path, $disk);
    }

    public function getPageUrl(string $mangaId, string $chapterId, int $page, int $expirationMinutes = 60, string $disk = null): string
    {
        $s3Path = $this->s3Service->buildPath($mangaId, $chapterId, (string)$page, $disk);
        
        if (!$this->s3Service->exists($s3Path, $disk)) {
            $result = $this->extractOnDemand($mangaId, $chapterId, $disk);
            
            if (isset($result['error'])) {
                throw new \Exception('Page not found');
            }
        }

        return $this->s3Service->getPageUrl($mangaId, $chapterId, $page, $expirationMinutes, $disk);
    }

    public function getPageCount(string $mangaId, string $chapterId, string $disk = null): int
    {
        $s3Path = $this->s3Service->buildPath($mangaId, $chapterId, null, $disk);
        $files = $this->s3Service->listFiles($s3Path, $disk);
        
        return count($files);
    }

    public function deletePages(string $mangaId, string $chapterId, string $disk = null): bool
    {
        $s3Path = $this->s3Service->buildPath($mangaId, $chapterId, null, $disk);
        $files = $this->s3Service->listFiles($s3Path, $disk);
        
        foreach ($files as $file) {
            $this->s3Service->delete($file, $disk);
        }

        return true;
    }

    protected function convertToWebP(string $imageContent, string $sourceFilename): string
    {
        $sourceExtension = strtolower(pathinfo($sourceFilename, PATHINFO_EXTENSION));
        $image = null;

        switch ($sourceExtension) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromstring($imageContent);
                break;
            case 'png':
                $image = @imagecreatefrompng($imageContent);
                break;
            case 'webp':
                $image = @imagecreatefromwebp($imageContent);
                break;
            case 'gif':
                $image = @imagecreatefromgif($imageContent);
                break;
        }

        if (!$image) {
            return $imageContent;
        }

        ob_start();
        imagewebp($image, null, 85);
        $webpContent = ob_get_contents();
        ob_end_clean();

        imagedestroy($image);

        return $webpContent ?: $imageContent;
    }
}
