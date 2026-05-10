<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaVersion extends Model
{
    protected $fillable = [
        'manga_id',
        'related_manga_id',
        'version_type',
        'language',
        'notes',
    ];

    protected $casts = [
        'notes' => 'array',
    ];

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }

    public function relatedManga(): BelongsTo
    {
        return $this->belongsTo(Manga::class, 'related_manga_id');
    }
}
