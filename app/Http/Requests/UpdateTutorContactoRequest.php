<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTutorContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nom_tco' => $this->nom_tco ? mb_strtoupper(trim($this->nom_tco), 'UTF-8') : null,
            'app_tco' => $this->app_tco ? mb_strtoupper(trim($this->app_tco), 'UTF-8') : null,
            'apm_tco' => $this->apm_tco ? mb_strtoupper(trim($this->apm_tco), 'UTF-8') : null,
            'par_tco' => $this->par_tco ? mb_strtoupper(trim($this->par_tco), 'UTF-8') : null,
            'cor_tco' => $this->cor_tco ? mb_strtolower(trim($this->cor_tco), 'UTF-8') : null,
            'tel_tco' => $this->tel_tco ? trim($this->tel_tco) : null,
            'etl_tco' => $this->boolean('etl_tco'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nom_tco' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'app_tco' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'apm_tco' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'par_tco' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL\s]+$/u'],
            'tel_tco' => ['required', 'string', 'min:7', 'max:20', 'regex:/^\d+$/'],
            'cor_tco' => ['nullable', 'email', 'max:150'],
            'etl_tco' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_tco.regex' => 'El nombre solo puede contener letras y espacios.',
            'nom_tco.min' => 'El nombre debe tener al menos 2 caracteres.',
            'app_tco.regex' => 'El apellido paterno solo puede contener letras y espacios.',
            'app_tco.min' => 'El apellido paterno debe tener al menos 2 caracteres.',
            'apm_tco.regex' => 'El apellido materno solo puede contener letras y espacios.',
            'apm_tco.min' => 'El apellido materno debe tener al menos 2 caracteres.',
            'par_tco.regex' => 'El parentesco solo puede contener letras y espacios.',
            'par_tco.min' => 'El parentesco debe tener al menos 2 caracteres.',
            'tel_tco.regex' => 'El teléfono solo puede contener números.',
            'tel_tco.min' => 'El teléfono debe tener al menos 7 dígitos.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nom_tco' => 'nombre',
            'app_tco' => 'apellido paterno',
            'apm_tco' => 'apellido materno',
            'par_tco' => 'parentesco',
            'tel_tco' => 'teléfono',
            'cor_tco' => 'correo',
            'etl_tco' => 'tutor legal',
        ];
    }
}