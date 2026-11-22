<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Alta de cuenta landlord compartida por signup web y registration api
 * (spec-service-landlord-fase5). Solo name/email/password: `role`, trío
 * free-plan y demás columnas server-side nunca llegan por aquí (Seg-28).
 */
class CreateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ruta pública: la autorización es del flujo OTP, no del request
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'password' => Password::required(),
            'email' => 'required|email',
        ];
    }
}
