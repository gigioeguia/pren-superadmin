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
        'features' => 'json'
    ]);
}
