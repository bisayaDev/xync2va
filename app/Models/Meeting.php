<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'name',
        'start',
        'end',
        'video_url',
        'prompts',
    ];

    protected $casts = [
        'prompts' => 'array',
    ];

    public function passcodes(): HasMany
    {
        return $this->hasMany(Passcode::class);
    }

}
