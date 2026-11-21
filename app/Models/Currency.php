<?php

namespace App\Models;

use App\Observers\CurrencyObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(CurrencyObserver::class)]
class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'rate',
        'symbol',
        'position',
        'status',
        'is_default',
    ];

    protected $casts = [
        'default' => 'integer',
        'status' => 'integer',
    ];
}
