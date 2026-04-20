<?php

namespace App\Http\Requests;

use App\Models\InstitucionExterna;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstitucionExternaRequest extends FormRequest
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
        $institucion = $this->route('institucionExterna');

        return [
            'nom_iex' => [
                'required',
                'string',
                'max:150',
                Rule::unique('instituciones_externas', 'nom_iex')
                    ->ignore($institucion->cod_instituciones_externas, 'cod_instituciones_externas')
                    ->whereNull('fbj_iex'),
            ],
            'tip_iex' => 'required|in:' . implode(',', array_keys(InstitucionExterna::TIPOS)),
            'ciu_iex' => 'nullable|string|max:100',
            'tel_iex' => 'nullable|string|max:20',
            'cor_iex' => 'nullable|email|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nom_iex.required' => 'El nombre de la institución es requerido.',
            'nom_iex.unique' => 'Ya existe una institución con este nombre.',
            'nom_iex.max' => 'El nombre no puede exceder 150 caracteres.',
            'tip_iex.required' => 'El tipo de institución es requerido.',
            'tip_iex.in' => 'El tipo de institución seleccionado no es válido.',
            'ciu_iex.max' => 'La ciudad no puede exceder 100 caracteres.',
            'tel_iex.max' => 'El teléfono no puede exceder 20 caracteres.',
            'cor_iex.email' => 'El correo electrónico no es válido.',
            'cor_iex.max' => 'El correo no puede exceder 100 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge([
            'nom_iex' => $this->nom_iex ? strtoupper(trim((string) $this->nom_iex)) : null,
            'ciu_iex' => $this->ciu_iex ? ucwords(strtolower(trim((string) $this->ciu_iex))) : null,
        ]);
    }
}
