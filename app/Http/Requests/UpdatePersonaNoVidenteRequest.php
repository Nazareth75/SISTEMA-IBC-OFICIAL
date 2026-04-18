<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonaNoVidenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nom_pnv' => $this->nom_pnv ? mb_strtoupper(trim($this->nom_pnv), 'UTF-8') : null,
            'ape_pnv' => $this->ape_pnv ? mb_strtoupper(trim($this->ape_pnv), 'UTF-8') : null,
            'dir_pnv' => $this->dir_pnv ? mb_strtoupper(trim($this->dir_pnv), 'UTF-8') : null,
            'ciu_pnv' => $this->ciu_pnv ? mb_strtoupper(trim($this->ciu_pnv), 'UTF-8') : null,
            'cau_pnv' => $this->cau_pnv ? mb_strtoupper(trim($this->cau_pnv), 'UTF-8') : null,
            'obs_pnv' => $this->obs_pnv ? mb_strtoupper(trim($this->obs_pnv), 'UTF-8') : null,
            'cor_pnv' => $this->cor_pnv ? mb_strtolower(trim($this->cor_pnv), 'UTF-8') : null,
            'cii_pnv' => $this->cii_pnv ? preg_replace('/\D+/', '', $this->cii_pnv) : null,
            'tel_pnv' => $this->tel_pnv ? preg_replace('/\D+/', '', $this->tel_pnv) : null,
        ]);
    }

    public function rules(): array
    {
        $persona = $this->route('personaNoVidente');
        $personaId = $persona?->cod_personas_no_videntes ?? $persona;

        return [
            'nom_pnv' => ['required', 'string', 'max:100'],
            'ape_pnv' => ['required', 'string', 'max:100'],
            'cii_pnv' => [
                'required',
                'string',
                'max:20',
                Rule::unique('personas_no_videntes', 'cii_pnv')->ignore($personaId, 'cod_personas_no_videntes'),
            ],
            'ext_pnv' => ['nullable', 'string', 'max:10'],
            'fen_pnv' => ['required', 'date', 'before_or_equal:today'],
            'gen_pnv' => ['required', Rule::in(['MASCULINO', 'FEMENINO', 'OTRO'])],
            'tel_pnv' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('personas_no_videntes', 'tel_pnv')->ignore($personaId, 'cod_personas_no_videntes'),
            ],
            'cor_pnv' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('personas_no_videntes', 'cor_pnv')->ignore($personaId, 'cod_personas_no_videntes'),
            ],
            'dir_pnv' => ['nullable', 'string', 'max:250'],
            'ciu_pnv' => ['required', 'string', 'max:100'],
            'gdi_pnv' => ['required', Rule::in(['TOTAL', 'PARCIAL', 'BAJA VISIÓN'])],
            'cau_pnv' => ['nullable', 'string', 'max:150'],
            'aut_pnv' => ['required', Rule::in(['INDEPENDIENTE', 'SEMI-DEPENDIENTE', 'DEPENDIENTE'])],
            'fot_pnv' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'est_pnv' => ['required', Rule::in(['PENDIENTE', 'ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'EGRESADO'])],
            'obs_pnv' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'cii_pnv.unique' => 'La cédula de identidad ya está registrada.',
            'cor_pnv.unique' => 'El correo ya está registrado.',
            'tel_pnv.unique' => 'El teléfono ya está registrado.',
            'fot_pnv.max' => 'La foto no debe superar los 2MB.',
            'fot_pnv.mimes' => 'La foto debe ser JPG o PNG.',
            'fen_pnv.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        ];
    }
}