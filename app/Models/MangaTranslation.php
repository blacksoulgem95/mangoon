<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\MangaTranslationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'manga_id',
        'language_code',
        'title',
        'alternative_titles',
        'synopsis',
        'description',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the manga that owns the translation.
     */
    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }

    /**
     * Get the language for this translation.
     */
    public function language(): ?Language
    {
        return Language::findByCode($this->language_code);
    }

    /**
     * Scope a query to filter by language code.
     */
    public function scopeByLanguage($query, string $languageCode): void
    {
        $query->where('language_code', $languageCode);
    }

    /**
     * Scope a query to search by title.
     */
    public function scopeSearchTitle($query, string $search): void
    {
        $query->where('title', 'like', "%{$search}%")
            ->orWhere('alternative_titles', 'like', "%{$search}%");
    }
}
