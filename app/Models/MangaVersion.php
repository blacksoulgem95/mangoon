<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaVersion extends Model
{
    /** @use HasFactory<\Database\Factories\MangaVersionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'manga_id',
        'related_manga_id',
        'relationship_type',
        'language_code',
        'notes',
        'sort_order',
        'is_primary',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Get the manga that owns this version relationship.
     */
    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }

    /**
     * Get the related manga for this version.
     */
    public function relatedManga(): BelongsTo
    {
        return $this->belongsTo(Manga::class, 'related_manga_id');
    }

    /**
     * Get the language for this version.
     */
    public function language(): ?Language
    {
        if ($this->language_code === null) {
            return null;
        }

        return Language::findByCode($this->language_code);
    }

    /**
     * Scope a query to filter by relationship type.
     */
    public function scopeByType($query, string $type): void
    {
        $query->where('relationship_type', $type);
    }

    /**
     * Scope a query to only include translations.
     */
    public function scopeTranslations($query): void
    {
        $query->where('relationship_type', 'translation');
    }

    /**
     * Scope a query to only include adaptations.
     */
    public function scopeAdaptations($query): void
    {
        $query->where('relationship_type', 'adaptation');
    }

    /**
     * Scope a query to only include spin-offs.
     */
    public function scopeSpinOffs($query): void
    {
        $query->where('relationship_type', 'spin-off');
    }

    /**
     * Scope a query to only include primary versions.
     */
    public function scopePrimary($query): void
    {
        $query->where('is_primary', true);
    }

    /**
     * Scope a query to filter by language code.
     */
    public function scopeByLanguage($query, string $languageCode): void
    {
        $query->where('language_code', $languageCode);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query): void
    {
        $query->orderBy('sort_order');
    }

    /**
     * Check if this is a translation relationship.
     */
    public function isTranslation(): bool
    {
        return $this->relationship_type === 'translation';
    }

    /**
     * Check if this is an adaptation relationship.
     */
    public function isAdaptation(): bool
    {
        return $this->relationship_type === 'adaptation';
    }

    /**
     * Check if this is a spin-off relationship.
     */
    public function isSpinOff(): bool
    {
        return $this->relationship_type === 'spin-off';
    }

    /**
     * Set this version as primary.
     */
    public function setAsPrimary(): bool
    {
        // Unset any existing primary versions for the same manga and relationship type
        static::where('manga_id', $this->manga_id)
            ->where('relationship_type', $this->relationship_type)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this as primary
        $this->is_primary = true;

        return $this->save();
    }
}
