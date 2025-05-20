<?php

namespace App\Models;

use App\Enums\FrequencyType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'city',
        'frequency',
        'token',
        'last_sent_at',
    ];

    protected $casts = [
        'frequency' => FrequencyType::class,
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
