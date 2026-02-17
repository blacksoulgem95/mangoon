<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Chapter extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'manga_id',
        'slug',
        'chapter_number',
        'title',
        'volume_number',
        'notes',
        'release_date',
        'cbz_file_path',
        'storage_disk',
        'file_size',
        'page_count',
        'views_count',
        'sort_order',
        'metadata',
        'is_active',
        'is_premium',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'volume_number' => 'integer',
            'file_size' => 'integer',
            'page_count' => 'integer',
            'views_count' => 'integer',
            'sort_order' => 'integer',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
        ];
    }

    /**
     * Get the manga that owns the chapter.
     */
    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }

    /**
     * Get the storage disk instance.
     */
    public function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk($this->storage_disk);
    }

    /**
     * Get the full URL or path to the CBZ file.
     */
    public function getCbzUrl(): string
    {
        return $this->disk()->url($this->cbz_file_path);
    }

    /**
     * Check if the CBZ file exists.
     */
    public function cbzExists(): bool
    {
        return $this->disk()->exists($this->cbz_file_path);
    }

    /**
     * Extract and get pages from CBZ file.
     *
     * @return array<int, array{name: string, data: string, extension: string}>
     */
    public function extractPages(): array
    {
        if (! $this->cbzExists()) {
            return [];
        }

        $cbzPath = $this->disk()->path($this->cbz_file_path);
        $zip = new \ZipArchive;

        if ($zip->open($cbzPath) !== true) {
            return [];
        }

        $pages = [];
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($extension, $imageExtensions)) {
                $pages[] = [
                    'name' => basename($filename),
                    'data' => $zip->getFromIndex($i),
                    'extension' => $extension,
                    'index' => $i,
                ];
            }
        }

        $zip->close();

        // Sort pages by filename naturally
        usort($pages, function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        return $pages;
    }

    /**
     * Get a specific page from the CBZ file.
     */
    public function getPage(int $pageNumber): ?array
    {
        $pages = $this->extractPages();

        return $pages[$pageNumber] ?? null;
    }

    /**
     * Update the page count from the CBZ file.
     */
    public function updatePageCount(): bool
    {
        $pages = $this->extractPages();
        $this->page_count = count($pages);

        return $this->save();
    }

    /**
     * Increment the views count.
     */
    public function incrementViews(int $count = 1): bool
    {
        return $this->increment('views_count', $count);
    }

    /**
     * Scope a query to only include active chapters.
     */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include premium chapters.
     */
    public function scopePremium($query): void
    {
        $query->where('is_premium', true);
    }

    /**
     * Scope a query to only include free chapters.
     */
    public function scopeFree($query): void
    {
        $query->where('is_premium', false);
    }

    /**
     * Scope a query to filter by manga.
     */
    public function scopeByManga($query, int $mangaId): void
    {
        $query->where('manga_id', $mangaId);
    }

    /**
     * Scope a query to filter by volume.
     */
    public function scopeByVolume($query, int $volumeNumber): void
    {
        $query->where('volume_number', $volumeNumber);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query): void
    {
        $query->orderBy('sort_order')->orderBy('chapter_number');
    }

    /**
     * Scope a query to order by latest.
     */
    public function scopeLatest($query): void
    {
        $query->orderByDesc('release_date')->orderByDesc('created_at');
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the previous chapter.
     */
    public function previous(): ?self
    {
        return static::where('manga_id', $this->manga_id)
            ->where('sort_order', '<', $this->sort_order)
            ->active()
            ->ordered()
            ->orderByDesc('sort_order')
            ->first();
    }

    /**
     * Get the next chapter.
     */
    public function next(): ?self
    {
        return static::where('manga_id', $this->manga_id)
            ->where('sort_order', '>', $this->sort_order)
            ->active()
            ->ordered()
            ->first();
    }

    /**
     * Get formatted chapter display name.
     */
    public function displayName(): string
    {
        $name = "Chapter {$this->chapter_number}";

        if ($this->volume_number) {
            $name = "Vol. {$this->volume_number} " . $name;
        }

        if ($this->title) {
            $name .= ": {$this->title}";
        }

        return $name;
    }
}
