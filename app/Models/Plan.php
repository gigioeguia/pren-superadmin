<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscriptionName',
        'duration',
        'duration_type',
        'offerPrice',
        'subscriptionPrice',
        'number_of_property',
        'number_of_tenant',
        'number_of_labor',
        'features',
        'image',
        'status',
    ];

    protected $casts = ([
        'features' => 'json',
    ]);

    public function durationInDays(): int
    {
        return match ($this->duration_type) {
            'month' => (int) $this->duration * 30,
            'year' => (int) $this->duration * 365,
            'day' => (int) $this->duration,
            default => 0,
        };
    }
}
