<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'paid_by',
        'received_by',
        'gateway_id',
        'invoice_no',
        'security_deposit_id',
        'withdraw_id',
        'plan_subscribe_id',
        'rent_payment_id',
        'date',
        'amount',
        'charge',
        'payment_method',
        'payment_type',
        'file',
        'type',
        'notes',
        'meta',
   ];

   public function paidBy() {

    return $this->belongsTo(User::class, 'paid_by');

   }
   public function received_by() {

    return $this->belongsTo(User::class);

   }
   public function gateway() {

    return $this->belongsTo(Gateway::class);

   }
   public function security_deposit() {

    return $this->belongsTo(SecurityDeposit::class);

   }

   public function withdraw() {

    return $this->belongsTo(Withdraw::class);

   }

   public function plan_subscribe() {

    return $this->belongsTo(PlanSubscribe::class);

   }

   public function rent_payment() {

    return $this->belongsTo(RentPayment::class);

   }

   public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $id = Transaction::count() + 1;
            $model->uid = str_pad($id, 5, '0', STR_PAD_LEFT);
        });
    }


}
