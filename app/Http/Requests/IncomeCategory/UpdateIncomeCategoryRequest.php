<?php

namespace App\Http\Requests\IncomeCategory;

use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncomeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('income_category') ?? $this->route('id');
        $id = $category instanceof IncomeCategory ? $category->id : $category;

        $user = $this->user();
        $landlordId = $user instanceof User ? $user->id : null;
        $isAdmin = $user instanceof User && in_array($user->role, ['admin', 'superadmin'], true);

        $uniqueWhere = function ($query) use ($isAdmin, $landlordId) {
            if ($isAdmin) {
                return $query->whereNull('landlord_id');
            }

            return $query->where('landlord_id', $landlordId);
        };

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('income_categories')->ignore($id)->where($uniqueWhere)],
            'description' => 'nullable|string',
            'status' => 'nullable',
        ];
    }
}
