<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalSource extends Model
{
    protected $fillable = [
        'manga_id',
        'source_name',
        'source_id',
        'metadata',
        'downloaded_at',
        'last_synced_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'downloaded_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'external_source_id');
    }
}
