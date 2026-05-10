<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'url',
        'icon',
        'is_active',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function manga(): HasMany
    {
        return $this->hasMany(Manga::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SourceTranslation::class);
    }

    public function getTranslatedName(): string
    {
        $translation = $this->translations()->first();
        return $translation?->name ?? $this->name;
    }
}
