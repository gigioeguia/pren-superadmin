<?php

namespace App\Models;

use App\Observers\OptionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(OptionObserver::class)]
class Option extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'status'];

    protected $casts = [
        'value' => 'json',
        'status' => 'integer',
    ];
}
