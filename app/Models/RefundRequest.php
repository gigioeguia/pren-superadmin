<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_id',
        'landlord_id',
        'tenant_id',
        'property_id',
        'reason_id',
        'invoice_no',
        'amount',
        'status',
        'refund_reason',
        'attachment',
        'bank_info'
    ];

    public function deposit() {

        return $this->belongsTo(SecurityDeposit::class);
    }

    public function property() {

        return $this->belongsTo(Property::class);
    }

    public function landlord() {

        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function tenant() {

        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function refund_reson() {

        return $this->belongsTo(RefundReason::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $id = RefundRequest::max('id') + 1;
            $model->invoice_no = str_pad($id, 6, '0', STR_PAD_LEFT);
        });
    }

    protected $casts = [
        'deposit_id' => 'integer',
        'landlord_id' => 'integer',
        'tenant_id' => 'integer',
        'property_id' => 'integer',
        'reason_id' => 'integer',
        'amount' => 'double',
        'bank_info' => 'json',
    ];
}
