<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'landlord_id', 'status'];

    public function landlord()
    {

        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'status' => 'integer',
    ];
}
