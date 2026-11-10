<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
