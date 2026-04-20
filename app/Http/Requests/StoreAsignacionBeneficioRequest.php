<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAsignacionBeneficioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'obs_abe' => $this->obs_abe ? trim($this->obs_abe) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'cod_afiliaciones_abe' => [
                'required',
                'integer',
                'exists:afiliaciones,cod_afiliaciones',
            ],
            'cod_beneficios_sociales_abe' => [
                'required',
                'integer',
                'exists:beneficios_sociales,cod_beneficios_sociales',
            ],
            'fas_abe' => [
                'required',
                'date',
            ],
            'ffi_abe' => [
                'nullable',
                'date',
                'after_or_equal:fas_abe',
            ],
            'est_abe' => [
                'required',
                Rule::in(['ACTIVO', 'SUSPENDIDO', 'FINALIZADO']),
            ],
            'obs_abe' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cod_afiliaciones_abe.required' => 'El afiliado es obligatorio.',
            'cod_afiliaciones_abe.integer' => 'El afiliado debe ser válido.',
            'cod_afiliaciones_abe.exists' => 'El afiliado seleccionado no existe o está inactivo.',
            
            'cod_beneficios_sociales_abe.required' => 'El beneficio es obligatorio.',
            'cod_beneficios_sociales_abe.integer' => 'El beneficio debe ser válido.',
            'cod_beneficios_sociales_abe.exists' => 'El beneficio seleccionado no existe o está inactivo.',
            
            'fas_abe.required' => 'La fecha de asignación es obligatoria.',
            'fas_abe.date' => 'La fecha de asignación debe ser una fecha válida.',
            
            'ffi_abe.date' => 'La fecha de fin debe ser una fecha válida.',
            'ffi_abe.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de asignación.',
            
            'est_abe.required' => 'El estado es obligatorio.',
            'est_abe.in' => 'El estado seleccionado no es válido. Debe ser: ACTIVO, SUSPENDIDO o FINALIZADO.',
            
            'obs_abe.string' => 'Las observaciones deben ser texto.',
            'obs_abe.max' => 'Las observaciones no pueden exceder 500 caracteres.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que el beneficio esté activo
            $codigoBeneficio = $this->input('cod_beneficios_sociales_abe');
            if ($codigoBeneficio) {
                $beneficio = \App\Models\BeneficioSocial::find($codigoBeneficio);
                if ($beneficio && !$beneficio->estaActivo()) {
                    $validator->errors()->add(
                        'cod_beneficios_sociales_abe',
                        'El beneficio debe estar activo para ser asignado.'
                    );
                }
            }

            // Validar que la afiliación esté activa
            $codigoAfiliacion = $this->input('cod_afiliaciones_abe');
            if ($codigoAfiliacion) {
                $afiliacion = \App\Models\Afiliacion::find($codigoAfiliacion);
                if ($afiliacion && $afiliacion->est_afi !== 'ACTIVA') {
                    $validator->errors()->add(
                        'cod_afiliaciones_abe',
                        'La afiliación debe estar activa para asignar beneficios.'
                    );
                }
            }
        });
    }
}
