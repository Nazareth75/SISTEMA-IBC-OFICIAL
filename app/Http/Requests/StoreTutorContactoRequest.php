<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTutorContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nom_tco' => $this->nom_tco ? mb_strtoupper(trim($this->nom_tco), 'UTF-8') : null,
            'ape_tco' => $this->ape_tco ? mb_strtoupper(trim($this->ape_tco), 'UTF-8') : null,
            'par_tco' => $this->par_tco ? mb_strtoupper(trim($this->par_tco), 'UTF-8') : null,
            'cor_tco' => $this->cor_tco ? mb_strtolower(trim($this->cor_tco), 'UTF-8') : null,
            'tel_tco' => $this->tel_tco ? preg_replace('/\D+/', '', $this->tel_tco) : null,
            'etl_tco' => $this->boolean('etl_tco'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nom_tco' => ['required', 'string', 'max:100'],
            'ape_tco' => ['required', 'string', 'max:100'],
            'par_tco' => ['required', 'string', 'max:50'],
            'tel_tco' => ['required', 'string', 'max:20'],
            'cor_tco' => ['nullable', 'email', 'max:150'],
            'etl_tco' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nom_tco' => 'nombre',
            'ape_tco' => 'apellido',
            'par_tco' => 'parentesco',
            'tel_tco' => 'teléfono',
            'cor_tco' => 'correo',
            'etl_tco' => 'tutor legal',
        ];
    }
}