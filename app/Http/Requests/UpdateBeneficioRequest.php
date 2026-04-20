<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBeneficioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nom_bso' => $this->nom_bso ? mb_strtoupper(trim($this->nom_bso), 'UTF-8') : null,
            'des_bso' => $this->des_bso ? trim($this->des_bso) : null,
        ]);
    }

    public function rules(): array
    {
        $beneficio = $this->route('beneficio');

        return [
            'nom_bso' => [
                'required',
                'string',
                'max:150',
                Rule::unique('beneficios_sociales', 'nom_bso')
                    ->ignore($beneficio->cod_beneficios_sociales, 'cod_beneficios_sociales'),
            ],
            'tip_bso' => [
                'required',
                Rule::in(['ECONÓMICO', 'MATERIAL', 'SERVICIO', 'EDUCATIVO', 'OTRO']),
            ],
            'des_bso' => [
                'nullable',
                'string',
                'max:250',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_bso.required' => 'El nombre del beneficio es obligatorio.',
            'nom_bso.string' => 'El nombre debe ser texto.',
            'nom_bso.max' => 'El nombre no puede exceder 150 caracteres.',
            'nom_bso.unique' => 'Ya existe otro beneficio con ese nombre.',
            
            'tip_bso.required' => 'El tipo de beneficio es obligatorio.',
            'tip_bso.in' => 'El tipo de beneficio seleccionado no es válido.',
            
            'des_bso.string' => 'La descripción debe ser texto.',
            'des_bso.max' => 'La descripción no puede exceder 250 caracteres.',
        ];
    }
}
