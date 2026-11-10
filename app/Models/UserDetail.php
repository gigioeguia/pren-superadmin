<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mykad',
        'gender',
        'birth_date',
        'mykad_id',
        'lang',
        'company_info',
        'address_info',
        'nominee_info',
        'emergency_contact',
        'workplace',
        'vehicles_info',
    ];


    protected $casts = [
        'company_info' => 'json',
        'address_info' => 'json',
        'nominee_info' => 'json',
        'emergency_contact' => 'json',
        'workplace' => 'json',
        'vehicles_info' => 'json',
        'mykad' => 'json',
    ];
}
