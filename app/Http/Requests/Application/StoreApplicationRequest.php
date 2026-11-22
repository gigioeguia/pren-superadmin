<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Store abierto a todo usuario autenticado: tenant_id se fuerza
        // server-side desde el actor (comportamiento actual, sin authorize).
        return true;
    }

    /**
     * Reglas del store api tenant (antes inline en el controlador).
     * unit_id pasa de leerse sin validar a nullable|integer — desviación
     * documentada en el spec §11 (input no-numérico: 404/500 actual → 422).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'property_id' => 'required|integer',
            'unit_id' => 'nullable|integer',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'rejected_cause' => 'nullable|string',
        ];
    }
}
