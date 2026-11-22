<?php

namespace App\Http\Requests\Rent;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas compartidas por ambos canales (spec §5.8: idénticas hoy).
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:users,id',
            'property_id' => 'required|exists:properties,id',
            'unit_id' => 'nullable|exists:units,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'landlord_agreement' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];
    }
}
