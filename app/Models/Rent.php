<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    use HasFactory;

    protected $fillable = ['landlord_id', 'tenant_id', 'property_id', 'unit_id', 'monthly_rent', 'utility_bill', 'total_amount', 'start_date', 'end_date', 'landlord_agreement', 'tenant_agreement', 'status', 'cancel_info'];

    protected $casts = [
        'landlord_id' => 'integer',
        'tenant_id' => 'integer',
        'property_id' => 'integer',
        'unit_id' => 'integer',
        'monthly_rent' => 'double',
        'utility_bill' => 'double',
        'total_amount' => 'double',
        'cancel_info' => 'json',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class);
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function security_deposit()
    {
        return $this->hasOne(SecurityDeposit::class, 'rent_id');
    }

    public function rent_payments()
    {
        return $this->hasMany(RentPayment::class, 'rent_id');
    }
}
