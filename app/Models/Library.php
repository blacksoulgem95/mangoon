<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Library extends Model
{
    /** @use HasFactory<\Database\Factories\LibraryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'icon',
        'color',
        'is_active',
        'is_public',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the translations for the library.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(LibraryTranslation::class);
    }

    /**
     * Get the mangas in this library.
     */
    public function mangas(): BelongsToMany
    {
        return $this->belongsToMany(Manga::class, 'library_manga')
            ->withPivot('sort_order', 'is_featured', 'added_at')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Scope a query to only include active libraries.
     */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include public libraries.
     */
    public function scopePublic($query): void
    {
        $query->where('is_public', true);
    }

    /**
     * Scope a query to only include active and public libraries.
     */
    public function scopeAvailable($query): void
    {
        $query->where('is_active', true)->where('is_public', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query): void
    {
        $query->orderBy('sort_order');
    }

    /**
     * Get translation for a specific language.
     */
    public function getTranslation(?string $languageCode = null): ?LibraryTranslation
    {
        if ($languageCode === null) {
            $languageCode = app()->getLocale();
        }

        return $this->translations()->where('language_code', $languageCode)->first();
    }

    /**
     * Get name in a specific language or fallback.
     */
    public function getName(?string $languageCode = null): string
    {
        $translation = $this->getTranslation($languageCode);

        if ($translation) {
            return $translation->name;
        }

        // Fallback to first available translation or slug
        $fallback = $this->translations()->first();

        return $fallback?->name ?? $this->slug;
    }

    /**
     * Get description in a specific language or fallback.
     */
    public function getDescription(?string $languageCode = null): ?string
    {
        $translation = $this->getTranslation($languageCode);

        if ($translation) {
            return $translation->description;
        }

        // Fallback to first available translation
        $fallback = $this->translations()->first();

        return $fallback?->description;
    }

    /**
     * Check if library has a specific manga.
     */
    public function hasManga(int|string $mangaIdOrSlug): bool
    {
        if (is_numeric($mangaIdOrSlug)) {
            return $this->mangas()->where('mangas.id', $mangaIdOrSlug)->exists();
        }

        return $this->mangas()->where('mangas.slug', $mangaIdOrSlug)->exists();
    }

    /**
     * Get featured mangas in this library.
     */
    public function featuredMangas(): BelongsToMany
    {
        return $this->mangas()->wherePivot('is_featured', true);
    }

    /**
     * Add a manga to this library.
     */
    public function addManga(int|Manga $manga, array $attributes = []): void
    {
        $mangaId = $manga instanceof Manga ? $manga->id : $manga;

        $this->mangas()->attach($mangaId, array_merge([
            'added_at' => now(),
            'sort_order' => 0,
            'is_featured' => false,
        ], $attributes));
    }

    /**
     * Remove a manga from this library.
     */
    public function removeManga(int|Manga $manga): void
    {
        $mangaId = $manga instanceof Manga ? $manga->id : $manga;

        $this->mangas()->detach($mangaId);
    }

    /**
     * Get manga count in this library.
     */
    public function getMangaCount(): int
    {
        return $this->mangas()->count();
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
