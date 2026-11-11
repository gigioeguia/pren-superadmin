<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyDetail extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'furnishing', 'facilities', 'parking_lot', 'amenities', 'full_name', 'phone', 'email', 'floorplan_image', 'residential_type', 'property_type', 'property_info', 'tenant_preference'];

    public function property()
    {

        return $this->belongsTo(Property::class);
    }

    public function facilities(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => array_map('intval', json_decode($value, true) ?? [])
        );
    }

    public function amenities(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => array_map('intval', json_decode($value, true) ?? [])
        );
    }

    protected $casts = [
        'tenant_preference' => 'json',
        'property_info' => 'json',
        'phone' => 'json',
        'facilities' => 'json',
        'amenities' => 'json',
    ];
}
