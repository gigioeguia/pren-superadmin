<?php

namespace App\Http\Requests\WithdrawMethod;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'min_amount' => 'required|numeric',
            'max_amount' => 'required|numeric',
            'charge' => 'required|numeric',
            'charge_type' => 'required|string',
            'instructions' => 'nullable|string',
            'meta' => 'nullable|array',
            'status' => 'nullable',
        ];
    }
}
