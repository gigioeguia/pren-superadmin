<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;

class StoreLandlordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso resuelto por middleware `permission:landlords-create`
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
            'phone' => 'nullable|array',
            'phone.mobile_no' => 'nullable|string|max:15',
            'mobile_code' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.mobile_no.max' => 'The phone must not be greater than 15 characters.',
        ];
    }
}
