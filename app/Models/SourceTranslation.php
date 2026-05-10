<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SourceTranslation extends Model
{
    protected $fillable = [
        'source_id',
        'locale',
        'name',
        'description',
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }
}
