<?php

namespace App\Models;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expense_category_id',
        'landlord_id',
        'amount',
        'expense_for',
        'payment_type',
        'reference_no',
        'note',
        'expense_date',
    ];

    public function category() : BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function landlord() : BelongsTo
    {
        return $this->belongsTo(User::class,'landlord_id');
    }
}
