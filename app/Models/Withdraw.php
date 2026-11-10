<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_method_id',
        'user_id',
        'amount',
        'charge',
        'status',
        'notes',
        'reason',
        'invoice_no'
    ];


    function account_info()
    {
        return $this->belongsTo(UserMethod::class, 'user_method_id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::created(function ($model) {
            $model->invoice_no = str_pad($model->id, 5, '0', STR_PAD_LEFT);
            $model->save();
        });
    }


    protected $casts = [
        'amount' => 'double',
        'charge' => 'double',
        'user_method_id' => 'integer',
        'user_id' => 'integer',
    ];
}
