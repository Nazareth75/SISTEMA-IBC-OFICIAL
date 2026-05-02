<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonaNoVidenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nom_pnv' => $this->nom_pnv ? mb_strtoupper(trim($this->nom_pnv), 'UTF-8') : null,
            'app_pnv' => $this->app_pnv ? mb_strtoupper(trim($this->app_pnv), 'UTF-8') : null,
            'apm_pnv' => $this->apm_pnv ? mb_strtoupper(trim($this->apm_pnv), 'UTF-8') : null,
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
        return [
            'nom_pnv' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'app_pnv' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'apm_pnv' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'cii_pnv' => ['required', 'string', 'min:5', 'max:20', 'regex:/^\d+$/', 'unique:personas_no_videntes,cii_pnv'],
            'ext_pnv' => ['nullable', Rule::in(['LP', 'CB', 'SC', 'OR', 'PT', 'TJ', 'CH', 'BE', 'PD'])],
            'fen_pnv' => ['required', 'date'],
            'gen_pnv' => ['required', Rule::in(['MASCULINO', 'FEMENINO', 'OTRO'])],
            'tel_pnv' => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^\d+$/', 'unique:personas_no_videntes,tel_pnv'],
            'cor_pnv' => ['nullable', 'email', 'max:150', 'unique:personas_no_videntes,cor_pnv'],
            'dir_pnv' => ['nullable', 'string', 'max:250'],
            'ciu_pnv' => ['required', Rule::in(['LA PAZ', 'COCHABAMBA', 'SANTA CRUZ', 'ORURO', 'POTOSI', 'TARIJA', 'CHUQUISACA', 'BENI', 'PANDO'])],
            'gdi_pnv' => ['required', Rule::in(['TOTAL', 'PARCIAL', 'BAJA VISIÓN'])],
            'cau_pnv' => ['nullable', 'string', 'max:150'],
            'aut_pnv' => ['required', Rule::in(['INDEPENDIENTE', 'SEMI-DEPENDIENTE', 'DEPENDIENTE'])],
            'fot_pnv' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'est_pnv' => ['nullable', Rule::in(['PENDIENTE', 'ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'EGRESADO'])],
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
            'nom_pnv.regex' => 'EL NOMBRE SOLO PUEDE CONTENER LETRAS Y ESPACIOS.',
            'nom_pnv.min' => 'EL NOMBRE DEBE TENER AL MENOS 2 CARACTERES.',
            'app_pnv.regex' => 'EL APELLIDO PATERNO SOLO PUEDE CONTENER LETRAS Y ESPACIOS.',
            'app_pnv.min' => 'EL APELLIDO PATERNO DEBE TENER AL MENOS 2 CARACTERES.',
            'apm_pnv.regex' => 'EL APELLIDO MATERNO SOLO PUEDE CONTENER LETRAS Y ESPACIOS.',
            'apm_pnv.min' => 'EL APELLIDO MATERNO DEBE TENER AL MENOS 2 CARACTERES.',
            'cii_pnv.regex' => 'LA CÉDULA DE IDENTIDAD SOLO PUEDE CONTENER NÚMEROS.',
            'cii_pnv.unique' => 'LA CÉDULA DE IDENTIDAD YA ESTÁ REGISTRADA.',
            'tel_pnv.regex' => 'EL TELÉFONO SOLO PUEDE CONTENER NÚMEROS.',
            'tel_pnv.min' => 'EL TELÉFONO DEBE TENER AL MENOS 7 DÍGITOS.',
            'tel_pnv.unique' => 'EL TELÉFONO YA ESTÁ REGISTRADO.',
            'cor_pnv.unique' => 'EL CORREO YA ESTÁ REGISTRADO.',
            'fot_pnv.max' => 'LA FOTO NO DEBE SUPERAR LOS 2MB.',
            'fot_pnv.mimes' => 'LA FOTO DEBE SER JPG O PNG.',
            'ext_pnv.in' => 'LA EXTENSIÓN SELECCIONADA NO ES VÁLIDA.',
            'ciu_pnv.in' => 'LA CIUDAD SELECCIONADA NO ES VÁLIDA.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nom_pnv' => 'nombre',
            'app_pnv' => 'apellido paterno',
            'apm_pnv' => 'apellido materno',
            'cii_pnv' => 'cédula de identidad',
            'ext_pnv' => 'extensión',
            'fen_pnv' => 'fecha de nacimiento',
            'gen_pnv' => 'género',
            'tel_pnv' => 'teléfono',
            'cor_pnv' => 'correo',
            'dir_pnv' => 'dirección',
            'ciu_pnv' => 'ciudad',
            'gdi_pnv' => 'grado de discapacidad',
            'cau_pnv' => 'causa',
            'aut_pnv' => 'autonomía',
            'fot_pnv' => 'foto',
            'est_pnv' => 'estado',
            'obs_pnv' => 'observaciones',
        ];
    }
}