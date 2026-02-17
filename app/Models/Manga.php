<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manga extends Model
{
    /** @use HasFactory<\Database\Factories\MangaFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "slug",
        "source_id",
        "author",
        "illustrator",
        "publication_year",
        "publication_date",
        "original_language",
        "status",
        "type",
        "cover_image",
        "banner_image",
        "total_chapters",
        "total_volumes",
        "rating",
        "views_count",
        "favorites_count",
        "isbn",
        "publisher",
        "metadata",
        "is_active",
        "is_featured",
        "is_mature",
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "publication_date" => "date",
            "publication_year" => "integer",
            "total_chapters" => "integer",
            "total_volumes" => "integer",
            "rating" => "decimal:2",
            "views_count" => "integer",
            "favorites_count" => "integer",
            "metadata" => "array",
            "is_active" => "boolean",
            "is_featured" => "boolean",
            "is_mature" => "boolean",
        ];
    }

    /**
     * Get the source that the manga belongs to.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Get the translations for the manga.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(MangaTranslation::class);
    }

    /**
     * Get the version relationships for this manga.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(MangaVersion::class);
    }

    /**
     * Get the related manga versions (as the related manga).
     */
    public function relatedVersions(): HasMany
    {
        return $this->hasMany(MangaVersion::class, "related_manga_id");
    }

    /**
     * Get the chapters for the manga.
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->ordered();
    }

    /**
     * Get the tags for the manga.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, "manga_tag")
            ->withPivot("sort_order")
            ->withTimestamps()
            ->orderByPivot("sort_order");
    }

    /**
     * Get the categories for the manga.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, "category_manga")
            ->withPivot("sort_order")
            ->withTimestamps()
            ->orderByPivot("sort_order");
    }

    /**
     * Get the libraries that contain this manga.
     */
    public function libraries(): BelongsToMany
    {
        return $this->belongsToMany(Library::class, "library_manga")
            ->withPivot("sort_order", "is_featured", "added_at")
            ->withTimestamps()
            ->orderByPivot("sort_order");
    }

    /**
     * Scope a query to only include active manga.
     */
    public function scopeActive($query): void
    {
        $query->where("is_active", true);
    }

    /**
     * Scope a query to only include featured manga.
     */
    public function scopeFeatured($query): void
    {
        $query->where("is_featured", true);
    }

    /**
     * Scope a query to only include mature manga.
     */
    public function scopeMature($query): void
    {
        $query->where("is_mature", true);
    }

    /**
     * Scope a query to only include safe manga (not mature).
     */
    public function scopeSafe($query): void
    {
        $query->where("is_mature", false);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, string $status): void
    {
        $query->where("status", $status);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, string $type): void
    {
        $query->where("type", $type);
    }

    /**
     * Scope a query to filter by original language.
     */
    public function scopeByLanguage($query, string $language): void
    {
        $query->where("original_language", $language);
    }

    /**
     * Scope a query to order by rating.
     */
    public function scopePopular($query): void
    {
        $query->orderByDesc("rating")->orderByDesc("views_count");
    }

    /**
     * Scope a query to order by views.
     */
    public function scopeMostViewed($query): void
    {
        $query->orderByDesc("views_count");
    }

    /**
     * Scope a query to order by favorites.
     */
    public function scopeMostFavorited($query): void
    {
        $query->orderByDesc("favorites_count");
    }

    /**
     * Scope a query to order by latest.
     */
    public function scopeLatest($query): void
    {
        $query->orderByDesc("created_at");
    }

    /**
     * Get translation for a specific language.
     */
    public function getTranslation(
        ?string $languageCode = null,
    ): ?MangaTranslation {
        if ($languageCode === null) {
            $languageCode = app()->getLocale();
        }

        return $this->translations()
            ->where("language_code", $languageCode)
            ->first();
    }

    /**
     * Get title in a specific language or fallback.
     */
    public function getTitle(?string $languageCode = null): string
    {
        $translation = $this->getTranslation($languageCode);

        if ($translation) {
            return $translation->title;
        }

        // Fallback to first available translation or slug
        $fallback = $this->translations()->first();

        return $fallback?->title ?? $this->slug;
    }

    /**
     * Get synopsis in a specific language or fallback.
     */
    public function getSynopsis(?string $languageCode = null): ?string
    {
        $translation = $this->getTranslation($languageCode);

        if ($translation) {
            return $translation->synopsis;
        }

        // Fallback to first available translation
        $fallback = $this->translations()->first();

        return $fallback?->synopsis;
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
     * Get all related manga versions by relationship type.
     */
    public function getRelatedVersions(
        string $type = "translation",
    ): \Illuminate\Database\Eloquent\Collection {
        return $this->versions()
            ->where("relationship_type", $type)
            ->with("relatedManga")
            ->get()
            ->pluck("relatedManga");
    }

    /**
     * Check if manga has a specific tag.
     */
    public function hasTag(int|string $tagIdOrSlug): bool
    {
        if (is_numeric($tagIdOrSlug)) {
            return $this->tags()->where("tags.id", $tagIdOrSlug)->exists();
        }

        return $this->tags()->where("tags.slug", $tagIdOrSlug)->exists();
    }

    /**
     * Check if manga is in a specific category.
     */
    public function inCategory(int|string $categoryIdOrSlug): bool
    {
        if (is_numeric($categoryIdOrSlug)) {
            return $this->categories()
                ->where("categories.id", $categoryIdOrSlug)
                ->exists();
        }

        return $this->categories()
            ->where("categories.slug", $categoryIdOrSlug)
            ->exists();
    }

    /**
     * Check if manga is in a specific library.
     */
    public function inLibrary(int|string $libraryIdOrSlug): bool
    {
        if (is_numeric($libraryIdOrSlug)) {
            return $this->libraries()
                ->where("libraries.id", $libraryIdOrSlug)
                ->exists();
        }

        return $this->libraries()
            ->where("libraries.slug", $libraryIdOrSlug)
            ->exists();
    }

    /**
     * Increment the views count.
     */
    public function incrementViews(int $count = 1): bool
    {
        return $this->increment("views_count", $count);
    }

    /**
     * Increment the favorites count.
     */
    public function incrementFavorites(int $count = 1): bool
    {
        return $this->increment("favorites_count", $count);
    }

    /**
     * Decrement the favorites count.
     */
    public function decrementFavorites(int $count = 1): bool
    {
        return $this->decrement("favorites_count", $count);
    }

    /**
     * Update the rating.
     */
    public function updateRating(float $newRating): bool
    {
        $this->rating = $newRating;

        return $this->save();
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName(): string
    {
        return "slug";
    }
}
