<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string',
            'phone' => 'nullable|array',
            'mobile_no' => 'nullable|string',
            'mobile_code' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'profile_type' => 'nullable|string',
            'gender' => 'nullable|string',
            'birth_date' => 'nullable|string',
            'mykad_id' => 'nullable|string',
            'company_info' => 'nullable',
            'address_info' => 'nullable',
            'nominee_info' => 'nullable',
            'emergency_contact' => 'nullable',
            'workplace' => 'nullable',
            'vehicles_info' => 'nullable',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
        ];
    }
}
