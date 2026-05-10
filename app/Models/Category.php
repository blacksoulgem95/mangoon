<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "slug",
        "parent_id",
        "color",
        "icon",
        "sort_order",
        "is_active",
    ];

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, "parent_id");
    }

    /**
     * Get the children categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, "parent_id");
    }

    /**
     * Get the translations for the category.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    /**
     * Get the translation for the category for a specific language.
     */
    public function translation(?string $languageCode = null): HasOne
    {
        return $this->hasOne(CategoryTranslation::class)->ofMany(
            "id",
            "max",
            fn($query) => $query->where(
                "language_code",
                $languageCode ?? app()->getLocale(),
            ),
        );
    }

    /**
     * Get the name of the category in the current or specified language.
     */
    public function getName(?string $languageCode = null): string
    {
        return $this->translation($languageCode)?->title ?? $this->name;
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query): void
    {
        $query->where("is_active", true);
    }

    /**
     * Get the route key name for the model.
     */
    public function getRouteKeyName(): string
    {
        return "slug";
    }
}
