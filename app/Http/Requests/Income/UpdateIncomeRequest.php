<?php

namespace App\Http\Requests\Income;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomeRequest extends FormRequest
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
            'income_for' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'income_date' => 'nullable|string',
            'note' => 'nullable|string',
            'income_category_id' => 'required|exists:income_categories,id',
        ];
    }
}
