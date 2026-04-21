<?php

namespace App\Http\Requests;

use App\Models\Capacitacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCapacitacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    public function rules(): array
    {
        return [
            'nom_cap'                    => 'required|string|max:150',
            'obj_cap'                    => 'nullable|string|max:300',
            'cod_tipos_capacitacion_cap' => [
                'required',
                'integer',
                Rule::exists('tipos_capacitacion', 'cod_tipos_capacitacion')
                    ->where('act_tca', 1)
                    ->whereNull('fbj_tca'),
            ],
            'fei_cap'          => 'nullable|date|date_format:Y-m-d',
            'fef_cap'          => 'nullable|date|date_format:Y-m-d|after_or_equal:fei_cap',
            'lug_cap'          => 'nullable|string|max:200',
            'cod_usuarios_cap' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'cod_usuarios'),
            ],
            'est_cap' => ['required', Rule::in(array_keys(Capacitacion::ESTADOS))],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_cap.required'                    => 'EL NOMBRE DE LA CAPACITACIÓN ES OBLIGATORIO.',
            'nom_cap.max'                         => 'EL NOMBRE NO PUEDE EXCEDER 150 CARACTERES.',
            'obj_cap.max'                         => 'EL OBJETIVO NO PUEDE EXCEDER 300 CARACTERES.',
            'cod_tipos_capacitacion_cap.required' => 'DEBE SELECCIONAR UN TIPO DE CAPACITACIÓN.',
            'cod_tipos_capacitacion_cap.integer'  => 'EL TIPO DE CAPACITACIÓN NO ES VÁLIDO.',
            'cod_tipos_capacitacion_cap.exists'   => 'EL TIPO SELECCIONADO NO EXISTE O ESTÁ INACTIVO.',
            'fei_cap.date'                        => 'LA FECHA DE INICIO DEBE SER UNA FECHA VÁLIDA.',
            'fei_cap.date_format'                 => 'LA FECHA DE INICIO DEBE TENER FORMATO YYYY-MM-DD.',
            'fef_cap.date'                        => 'LA FECHA DE FIN DEBE SER UNA FECHA VÁLIDA.',
            'fef_cap.date_format'                 => 'LA FECHA DE FIN DEBE TENER FORMATO YYYY-MM-DD.',
            'fef_cap.after_or_equal'              => 'LA FECHA DE FIN DEBE SER IGUAL O POSTERIOR A LA FECHA DE INICIO.',
            'lug_cap.max'                         => 'EL LUGAR NO PUEDE EXCEDER 200 CARACTERES.',
            'cod_usuarios_cap.exists'             => 'EL RESPONSABLE SELECCIONADO NO EXISTE EN EL SISTEMA.',
            'est_cap.required'                    => 'EL ESTADO DE LA CAPACITACIÓN ES OBLIGATORIO.',
            'est_cap.in'                          => 'EL ESTADO SELECCIONADO NO ES VÁLIDO.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'nom_cap' => $this->nom_cap ? strtoupper(trim($this->nom_cap)) : null,
            'lug_cap' => $this->lug_cap ? strtoupper(trim($this->lug_cap)) : null,
        ]);
    }
}
