<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_no',
        'rent_id',
        'gateway_id',
        'monthly_rent',
        'utility_bill',
        'charge',
        'landlord_balance',
        'total_amount',
        'late_fee',
        'rent_month',
        'rent_year',
        'payment_date',
        'payment_status',
        'payment_data',
        'note',
    ];

    public function rent(){
        return $this->belongsTo(Rent::class);
    }

    public function gateway(){
        return $this->belongsTo(Gateway::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $id = RentPayment::count() + 1;
            $model->invoice_no = "RP-" . str_pad($id, 5, '0', STR_PAD_LEFT);
        });
    }

    protected $casts = [
        'rent_id' => 'integer',
        'gateway_id' => 'integer',
        'monthly_rent' => 'double',
        'utility_bill' => 'double',
        'total_amount' => 'double',
        'landlord_balance' => 'double',
        'charge' => 'double',
        'payment_data' => 'json',
    ];
}
