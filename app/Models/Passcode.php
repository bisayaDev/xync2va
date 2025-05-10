<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passcode extends Model
{
    protected $guarded = [];
    protected $casts = [
        'logs' => 'array',
    ];


    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}
