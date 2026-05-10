<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCookie extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'cookies',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'cookies' => 'encrypted:array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function setActive(bool $active): void
    {
        $this->update(['is_active' => $active]);
    }
}
