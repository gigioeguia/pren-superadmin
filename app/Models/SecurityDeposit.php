<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'tenant_id',
        'landlord_id',
        'gateway_id',
        'rent_id',
        'property_id',
        'unit_id',
        'deposit_amount',
        'rent_advance',
        'utility_advance',
        'total_amount',
        'charge',
        'landlord_balance',
        'payment_date',
        'status',
        'payment_data',
        'note',
    ];

    public function landlord()
    {

        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function tenant()
    {

        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function property()
    {

        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function gateway()
    {

        return $this->belongsTo(Gateway::class);
    }

    public function rent()
    {
        return $this->belongsTo(Rent::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $id = SecurityDeposit::count() + 1;
            $model->invoice_no = str_pad($id, 5, '0', STR_PAD_LEFT);
        });
    }

    protected $casts = [
        'payment_data' => 'json',
    ];
}
