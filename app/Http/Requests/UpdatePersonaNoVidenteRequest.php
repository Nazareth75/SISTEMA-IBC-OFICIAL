<?php

namespace App\Http\Requests;

use Carbon\Carbon;
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
            'cii_pnv' => $this->cii_pnv ? trim($this->cii_pnv) : null,
            'tel_pnv' => $this->tel_pnv ? trim($this->tel_pnv) : null,
        ]);
    }

    public function rules(): array
    {
        $persona = $this->route('personaNoVidente');
        $personaId = $persona?->cod_personas_no_videntes ?? $persona;

        return [
            'nom_pnv' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'ape_pnv' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'cii_pnv' => [
                'required',
                'string',
                'min:5',
                'max:20',
                'regex:/^\d+$/',
                Rule::unique('personas_no_videntes', 'cii_pnv')->ignore($personaId, 'cod_personas_no_videntes'),
            ],
            'ext_pnv' => ['nullable', Rule::in(['LP', 'CB', 'SC', 'OR', 'PT', 'TJ', 'CH', 'BE', 'PD'])],
            'fen_pnv' => ['required', 'date'],
            'gen_pnv' => ['required', Rule::in(['MASCULINO', 'FEMENINO', 'OTRO'])],
            'tel_pnv' => [
                'nullable',
                'string',
                'min:7',
                'max:20',
                'regex:/^\d+$/',
                Rule::unique('personas_no_videntes', 'tel_pnv')->ignore($personaId, 'cod_personas_no_videntes'),
            ],
            'cor_pnv' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('personas_no_videntes', 'cor_pnv')->ignore($personaId, 'cod_personas_no_videntes'),
            ],
            'dir_pnv' => ['nullable', 'string', 'max:250'],
            'ciu_pnv' => ['required', Rule::in(['LA PAZ', 'COCHABAMBA', 'SANTA CRUZ', 'ORURO', 'POTOSI', 'TARIJA', 'CHUQUISACA', 'BENI', 'PANDO'])],
            'gdi_pnv' => ['required', Rule::in(['TOTAL', 'PARCIAL', 'BAJA VISIÓN'])],
            'cau_pnv' => ['nullable', 'string', 'max:150'],
            'aut_pnv' => ['required', Rule::in(['INDEPENDIENTE', 'SEMI-DEPENDIENTE', 'DEPENDIENTE'])],
            'fot_pnv' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'est_pnv' => ['required', Rule::in(['PENDIENTE', 'ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'EGRESADO'])],
            'obs_pnv' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fecha = $this->input('fen_pnv');

            if (!$fecha) {
                return;
            }

            try {
                $nacimiento = Carbon::parse($fecha);
                $hoy = Carbon::today();
                $edad = $nacimiento->age;

                if ($nacimiento->isSameDay($hoy)) {
                    $validator->errors()->add('fen_pnv', 'La fecha de nacimiento no puede ser hoy.');
                }

                if ($nacimiento->greaterThanOrEqualTo($hoy)) {
                    $validator->errors()->add('fen_pnv', 'La fecha de nacimiento debe ser anterior a hoy.');
                }

                if ($edad < 5) {
                    $validator->errors()->add('fen_pnv', 'La persona debe tener al menos 5 años de edad.');
                }

                if ($edad > 120) {
                    $validator->errors()->add('fen_pnv', 'La edad no puede ser mayor a 120 años.');
                }
            } catch (\Throwable $e) {
                $validator->errors()->add('fen_pnv', 'La fecha de nacimiento no es válida.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'nom_pnv.regex' => 'El nombre solo puede contener letras y espacios.',
            'nom_pnv.min' => 'El nombre debe tener al menos 2 caracteres.',
            'ape_pnv.regex' => 'El apellido solo puede contener letras y espacios.',
            'ape_pnv.min' => 'El apellido debe tener al menos 2 caracteres.',
            'cii_pnv.regex' => 'La cédula de identidad solo puede contener números.',
            'cii_pnv.unique' => 'La cédula de identidad ya está registrada.',
            'tel_pnv.regex' => 'El teléfono solo puede contener números.',
            'tel_pnv.min' => 'El teléfono debe tener al menos 7 dígitos.',
            'tel_pnv.unique' => 'El teléfono ya está registrado.',
            'cor_pnv.unique' => 'El correo ya está registrado.',
            'fot_pnv.max' => 'La foto no debe superar los 2MB.',
            'fot_pnv.mimes' => 'La foto debe ser JPG o PNG.',
            'ext_pnv.in' => 'La extensión seleccionada no es válida.',
            'ciu_pnv.in' => 'La ciudad seleccionada no es válida.',
        ];
    }
}