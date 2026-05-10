<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OauthAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'token',
        'refresh_token',
        'expires_at',
        'additional_data',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'additional_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
