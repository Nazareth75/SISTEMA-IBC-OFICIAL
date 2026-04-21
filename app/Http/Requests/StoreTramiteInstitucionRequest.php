<?php

namespace App\Http\Requests;

use App\Models\Afiliacion;
use App\Models\InstitucionExterna;
use App\Models\TramiteInstitucion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTramiteInstitucionRequest extends FormRequest
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
            'cod_afiliaciones_tin' => 'required|integer|exists:afiliaciones,cod_afiliaciones',
            'cod_instituciones_externas_tin' => 'required|integer|exists:instituciones_externas,cod_instituciones_externas',
            'tti_tin' => 'required|in:' . implode(',', array_keys(TramiteInstitucion::TIPOS)),
            'des_tin' => 'required|string|max:500',
            'fei_tin' => 'required|date|date_format:Y-m-d',
            'fer_tin' => 'nullable|date|date_format:Y-m-d|after_or_equal:fei_tin',
            'obs_tin' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cod_afiliaciones_tin.required' => 'El afiliado es requerido.',
            'cod_afiliaciones_tin.exists' => 'El afiliado seleccionado no existe o no es válido.',
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
            // RN-01: Validar que la afiliación esté ACTIVA
            $afiliacion = Afiliacion::find($this->cod_afiliaciones_tin);
            if ($afiliacion && $afiliacion->est_afi !== 'ACTIVA') {
                $validator->errors()->add('cod_afiliaciones_tin', 'La afiliación debe estar ACTIVA para registrar un trámite.');
            }

            // RN-02: Validar que la institución esté ACTIVA
            $institucion = InstitucionExterna::find($this->cod_instituciones_externas_tin);
            if ($institucion && !$institucion->estaActiva()) {
                $validator->errors()->add('cod_instituciones_externas_tin', 'La institución debe estar ACTIVA para registrar un trámite.');
            }
        });
    }
}
