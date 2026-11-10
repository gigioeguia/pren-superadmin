<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanSubscribe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'gateway_id',
        'plan_name',
        'price',
        'notes',
        'duration',
        'number_of_property',
        'number_of_tenant',
        'number_of_labor',
        'payment_status'
 ];

    protected $casts = [
        'notes' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }
}
