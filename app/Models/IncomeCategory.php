<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_id',
        'name',
        'description',
        'status',
    ];

    public function landlord()
    {
        return $this->belongsTo(User::class,'landlord_id');
    }
}
