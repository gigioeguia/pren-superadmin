<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'expense_for' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'expense_date' => 'nullable|string',
            'note' => 'nullable|string',
            'expense_category_id' => 'required|exists:expense_categories,id',
        ];
    }
}
