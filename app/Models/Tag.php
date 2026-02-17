<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "slug",
        "color",
        "is_active",
        "sort_order",
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "is_active" => "boolean",
            "sort_order" => "integer",
        ];
    }

    /**
     * Get the translations for the tag.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TagTranslation::class);
    }

    /**
     * Get the mangas that have this tag.
     */
    public function mangas(): BelongsToMany
    {
        return $this->belongsToMany(Manga::class, "manga_tag")
            ->withPivot("sort_order")
            ->withTimestamps()
            ->orderByPivot("sort_order");
    }

    /**
     * Scope a query to only include active tags.
     */
    public function scopeActive($query): void
    {
        $query->where("is_active", true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query): void
    {
        $query->orderBy("sort_order");
    }

    /**
     * Scope a query to search by translated name.
     */
     */
    public function scopeSearchByName($query, string $search, ?string $languageCode = null): void
    {
        $query->whereHas("translations", function ($q) use ($search, $languageCode) {
            $q->where("name", "like", "%{$search}%");

            if ($languageCode) {
                $q->where("language_code", $languageCode);
            }
        });
    }

    /**
     * Scope a query to filter by language.
     */
     */
    public function scopeWithTranslation($query, ?string $languageCode = null): void
    {
        if ($languageCode === null) {
            $languageCode = app()->getLocale();
        }

        $query->with([
            "translations" => function ($q) use ($languageCode) {
                $q->where("language_code", $languageCode);
            },
        ]);
    }

    /**
     * Get translation for a specific language.
     */
    public function getTranslation(?string $languageCode = null): ?TagTranslation
    {
        if ($languageCode === null) {
            $languageCode = app()->getLocale();
        }

        return $this->translations()
            ->where("language_code", $languageCode)
            ->first();
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
     * Get manga count for this tag.
     */
    public function getMangaCount(): int
    {
        return $this->mangas()->count();
    }

    /**
     * Get popular tags (by manga count).
     */
    public static function popular(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
            ->withCount("mangas")
            ->orderByDesc("mangas_count")
            ->limit($limit)
            ->get();
    }

    /**
     * Check if tag is used by any manga.
     */
    public function isUsed(): bool
    {
        return $this->mangas()->exists();
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName(): string
    {
        return "slug";
    }

    /**
     * Create a tag with translation.
     */
    public static function createWithTranslation(
        string $slug,
        string $name,
        string $languageCode = "en",
        array $attributes = [],
    ): self {
        $tag = static::create(
            array_merge(
                [
                    "slug" => $slug,
                    "is_active" => true,
                    "sort_order" => 0,
                ],
                $attributes,
            ),
        );

        $tag->translations()->create([
            "language_code" => $languageCode,
            "name" => $name,
        ]);

        return $tag;
    }
}
