<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAfiliacionRequest extends FormRequest
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
            'cod_tipos_afiliado_afi' => ['required', 'integer', 'exists:tipos_afiliado,cod_tipos_afiliado'],
            'fia_afi' => ['required', 'date'],
            'fve_afi' => ['nullable', 'date', 'after_or_equal:fia_afi'],
            'est_afi' => ['required', Rule::in(['ACTIVA', 'INACTIVA', 'SUSPENDIDA', 'VENCIDA'])],
            'obs_afi' => ['nullable', 'string'],
        ];
    }
}