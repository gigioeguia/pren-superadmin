<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'unit_name',
        'floor_range',
        'bedroom',
        'bathroom',
        'kitchen',
        'balcony',
        'condition',
        'parking_lot',
        'unit_size',
        'facilities',
        'amenities',
        'tenant_preference',
        'description',
        'images',
        'monthly_rent',
        'sale_amount',
        'min_rental_value',
        'min_rental_unit',
        'security_deposit_value',
        'security_deposit_unit',
        'security_deposit',
        'utility_deposit',
        'late_fee',
        'late_fee_after_days',
    ];

    public function rent()
    {
        return $this->hasOne(Rent::class);
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
        'facilities' => 'json',
        'amenities' => 'json',
        'tenant_preference' => 'json',
        'images' => 'json',
        'monthly_rent' => 'double',
        'sale_amount' => 'double',
        'security_deposit' => 'double',
        'utility_deposit' => 'double',
        'late_fee' => 'double',
        'late_fee_after_days' => 'integer',
    ];

}
