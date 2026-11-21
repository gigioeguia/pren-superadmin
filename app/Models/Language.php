<?php

namespace App\Models;

use App\Observers\LanguageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(LanguageObserver::class)]
class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'order',
        'icon',
        'status',
        'is_default',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_default' => 'boolean',
    ];
}
