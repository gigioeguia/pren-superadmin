<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordWithdraw extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','withdraw_method_id','amount','is_paid','status','reject_cause','currency'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawMethod()
    {
        return $this->belongsTo(WithdrawMethod::class);
    }

    protected $casts = [
        'amount' => 'double',
        'is_paid' => 'integer',
        'status' => 'integer',
    ];
}
