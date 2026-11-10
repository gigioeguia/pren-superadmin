<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;
    protected $fillable = ['property_id', 'tenant_id', 'labor_id', 'title', 'description', 'notes', 'comments', 'total_costing', 'images', 'status'];


    public function property()
    {
       return $this->belongsTo(Property::class, 'property_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function labor()
    {
        return $this->belongsTo(Labor::class);
    }




    // public function maintenanceCost()
    // {
    //     return $this->hasMany(MaintenanceCost::class);
    // }

    protected $casts = [
        'images' => 'json',
        'property_id' => 'integer',
        'tenant_id' => 'integer',
        'labor_id' => 'integer',
        'total_costing' => 'double',
    ];

}
