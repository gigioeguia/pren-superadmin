<?php

namespace App\Http\Requests\Property;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'house_type_id' => 'nullable|exists:house_types,id',
            'state' => 'nullable',
            'city' => 'nullable',
            'building_name' => 'nullable',
            'utility_deposit' => 'nullable|numeric',
            'security_deposit' => 'nullable|numeric',
            'residential_type' => 'nullable',
            'address' => 'nullable',
            'postcode' => 'nullable|numeric',
            'floor_rang' => 'nullable|string',
            'bedroom' => 'nullable|string',
            'bathroom' => 'nullable|string',
            'property_size' => 'nullable|numeric',
            'furnishing' => 'nullable',
            'facility' => 'nullable',
            'amenity' => 'nullable',
            'property_title' => 'nullable',
            'property_description' => 'nullable',
            'property_rent' => 'nullable|numeric',
            'full_name' => 'required|string',
            'phone' => 'required|array',
            'email' => 'nullable|email',
            'property_type' => 'nullable|string',
            'land_size' => 'nullable|numeric',
            'unit_size' => 'nullable|numeric',
            'unit_number' => 'nullable|string',
            'country' => 'nullable|string',
            'room_size' => 'nullable|string',
            'tenant_preference' => 'nullable',
            'cover_image.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'floorplan_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        if ($this->requiresLandlordId()) {
            $rules['landlord_id'] = ['required', Rule::exists('users', 'id')->where('role', 'landlord')];
        }

        return $rules;
    }

    protected function requiresLandlordId(): bool
    {
        return $this->isAdmin() && ! $this->is('api/*');
    }

    protected function isAdmin(): bool
    {
        $user = $this->user();

        return $user instanceof User && in_array($user->role, ['admin', 'superadmin'], true);
    }
}
