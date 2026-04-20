<?php

namespace App\Http\Requests;

use App\Models\InstitucionExterna;
use App\Models\TramiteInstitucion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTramiteInstitucionRequest extends FormRequest
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
            'cod_instituciones_externas_tin' => 'required|integer|exists:instituciones_externas,cod_instituciones_externas',
            'tti_tin' => 'required|in:' . implode(',', array_keys(TramiteInstitucion::TIPOS)),
            'des_tin' => 'required|string|max:500',
            'fei_tin' => 'required|date|date_format:Y-m-d',
            'fer_tin' => 'nullable|date|date_format:Y-m-d|after_or_equal:fei_tin',
            'est_tin' => 'required|in:' . implode(',', array_keys(TramiteInstitucion::ESTADOS)),
            'obs_tin' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cod_instituciones_externas_tin.required' => 'La institución es requerida.',
            'cod_instituciones_externas_tin.exists' => 'La institución seleccionada no existe o no es válida.',
            'tti_tin.required' => 'El tipo de trámite es requerido.',
            'tti_tin.in' => 'El tipo de trámite seleccionado no es válido.',
            'des_tin.required' => 'La descripción del trámite es requerida.',
            'des_tin.max' => 'La descripción no puede exceder 500 caracteres.',
            'fei_tin.required' => 'La fecha de inicio es requerida.',
            'fei_tin.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fei_tin.date_format' => 'La fecha de inicio debe estar en formato YYYY-MM-DD.',
            'fer_tin.date' => 'La fecha de resolución debe ser una fecha válida.',
            'fer_tin.date_format' => 'La fecha de resolución debe estar en formato YYYY-MM-DD.',
            'fer_tin.after_or_equal' => 'La fecha de resolución debe ser igual o posterior a la fecha de inicio.',
            'est_tin.required' => 'El estado es requerido.',
            'est_tin.in' => 'El estado seleccionado no es válido.',
            'obs_tin.max' => 'Las observaciones no pueden exceder 500 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge([
            'des_tin' => $this->des_tin ? trim((string) $this->des_tin) : null,
            'obs_tin' => $this->obs_tin ? trim((string) $this->obs_tin) : null,
        ]);
    }

    /**
     * Perform custom validation checks.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $tramite = $this->route('tramiteInstitucion');

            // RN-07: Validar que RESUELTO no pueda revertirse a PENDIENTE
            if ($tramite->est_tin === 'RESUELTO' && in_array($this->est_tin, ['PENDIENTE'])) {
                $validator->errors()->add('est_tin', 'Un trámite RESUELTO no puede revertirse a PENDIENTE.');
            }

            // RN-02: Validar que la institución esté ACTIVA
            $institucion = InstitucionExterna::find($this->cod_instituciones_externas_tin);
            if ($institucion && !$institucion->estaActiva()) {
                $validator->errors()->add('cod_instituciones_externas_tin', 'La institución debe estar ACTIVA.');
            }
        });
    }
}
