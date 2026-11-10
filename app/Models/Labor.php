<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labor extends Model
{
    use HasFactory;
    protected $fillable =['landlord_id','name','email','phone','gender','salary','status'];

    protected $casts = [
        'salary' => 'double',
        'status' => 'integer',
        'phone' => 'json',
    ];

    public function landlord()
    {
        return $this->belongsTo(User::class,'landlord_id');
    }

    // public function maintenanceCost()
    // {
    //     return $this->hasMany(MaintenanceCost::class);
    // }
    // public static function boot() {
    //     parent::boot();

    //     static::deleting(function($labor) {
    //         $labor->maintenanceCost()->delete();
    //     });
    // }
}
