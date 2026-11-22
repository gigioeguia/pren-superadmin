<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLandlordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso resuelto por middleware `permission:landlords-update`
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // 'nullable' puro: claves que el original leía sin validar —
            // fluyen por validated() sin rechazar nada nuevo
            'name' => 'nullable',
            'gender' => 'nullable',
            'mykad_id' => 'nullable',
            'address_info' => 'nullable',
            'password' => 'nullable',
            'phone' => 'nullable|array',
            'mobile_no' => 'nullable|string|max:15',
            'mobile_code' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,'.$this->route('landlord'),
            'image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
        ];
    }
}
