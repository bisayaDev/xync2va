<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
      'documents' => 'array',
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return null;
        }

        return \Carbon\Carbon::parse($this->date_of_birth)->age;
    }

    public function medications()
    {
        return $this->hasMany(Event::class, 'client_id')->where('med_type', 'medication');
    }

    public function medical()
    {
        return $this->hasMany(Event::class, 'client_id')->where('med_type', 'medical');
    }

    public function appointments()
    {
        return $this->hasMany(Event::class, 'client_id');
    }
}
