<?php

namespace App\Http\Requests;

use App\Models\AccesoSalud;
use App\Models\InstitucionExterna;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccesoSaludRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cod_instituciones_externas_asa' => 'required|integer|exists:instituciones_externas,cod_instituciones_externas',
            'fea_asa' => 'required|date|date_format:Y-m-d',
            'mot_asa' => 'required|string|max:300',
            'est_asa' => 'required|in:' . implode(',', array_keys(AccesoSalud::ESTADOS)),
            'obs_asa' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cod_instituciones_externas_asa.required' => 'La institución es requerida.',
            'cod_instituciones_externas_asa.exists' => 'La institución seleccionada no existe o no es válida.',
            'fea_asa.required' => 'La fecha de acceso es requerida.',
            'fea_asa.date' => 'La fecha de acceso debe ser una fecha válida.',
            'fea_asa.date_format' => 'La fecha de acceso debe estar en formato YYYY-MM-DD.',
            'mot_asa.required' => 'El motivo del acceso es requerido.',
            'mot_asa.max' => 'El motivo no puede exceder 300 caracteres.',
            'est_asa.required' => 'El estado es requerido.',
            'est_asa.in' => 'El estado seleccionado no es válido.',
            'obs_asa.max' => 'Las observaciones no pueden exceder 500 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge([
            'mot_asa' => $this->mot_asa ? trim((string) $this->mot_asa) : null,
            'obs_asa' => $this->obs_asa ? trim((string) $this->obs_asa) : null,
        ]);
    }

    /**
     * Perform custom validation checks.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que la institución esté ACTIVA
            $institucion = InstitucionExterna::find($this->cod_instituciones_externas_asa);
            if ($institucion && !$institucion->estaActiva()) {
                $validator->errors()->add('cod_instituciones_externas_asa', 'La institución debe estar ACTIVA.');
            }
        });
    }
}
