<?php

namespace App\Models;

use App\Models\IncomeCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'income_category_id',
        'landlord_id',
        'amount',
        'income_for',
        'payment_type',
        'reference_no',
        'note',
        'income_date',
    ];

    public function category() : BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class, 'income_category_id');
    }

    public function landlord() : BelongsTo
    {
        return $this->belongsTo(User::class,'landlord_id');
    }
}
