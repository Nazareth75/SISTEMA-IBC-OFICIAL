<?php

namespace App\Http\Requests;

use App\Models\PersonaNoVidente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAfiliacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'obs_afi' => $this->obs_afi ? mb_strtoupper(trim($this->obs_afi), 'UTF-8') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'cod_personas_no_videntes_afi' => ['required', 'integer', 'exists:personas_no_videntes,cod_personas_no_videntes'],
            'cod_tipos_afiliado_afi' => ['required', 'integer', 'exists:tipos_afiliado,cod_tipos_afiliado'],
            'fia_afi' => ['required', 'date'],
            'fve_afi' => ['nullable', 'date', 'after_or_equal:fia_afi'],
            'est_afi' => ['nullable', Rule::in(['ACTIVA', 'INACTIVA', 'SUSPENDIDA', 'VENCIDA'])],
            'obs_afi' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $personaId = $this->input('cod_personas_no_videntes_afi');

            if (!$personaId) {
                return;
            }

            $persona = PersonaNoVidente::find($personaId);

            if (!$persona) {
                return;
            }

            if (!$persona->puedeSerAfiliada()) {
                $validator->errors()->add(
                    'cod_personas_no_videntes_afi',
                    'Solo se puede afiliar a personas en estado ACTIVO.'
                );
            }

            if ($persona->tieneAfiliacionActiva()) {
                $validator->errors()->add(
                    'cod_personas_no_videntes_afi',
                    'La persona ya tiene una afiliación ACTIVA.'
                );
            }
        });
    }
}