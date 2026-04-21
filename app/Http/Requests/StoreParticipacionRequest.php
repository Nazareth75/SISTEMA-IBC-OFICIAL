<?php

namespace App\Http\Requests;

use App\Models\ParticipacionCapacitacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParticipacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    public function rules(): array
    {
        return [
            'cod_personas_no_videntes_pca' => [
                'required',
                'integer',
                Rule::exists('personas_no_videntes', 'cod_personas_no_videntes')
                    ->whereNull('fbj_pnv'),
            ],
            'cod_capacitaciones_pca' => [
                'required',
                'integer',
                Rule::exists('capacitaciones', 'cod_capacitaciones')
                    ->whereNull('fbj_cap'),
            ],
            'fei_pca' => 'required|date|date_format:Y-m-d',
            'est_pca' => ['required', Rule::in(array_keys(ParticipacionCapacitacion::ESTADOS))],
            'obs_pca' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'cod_personas_no_videntes_pca.required' => 'DEBE SELECCIONAR UNA PERSONA.',
            'cod_personas_no_videntes_pca.integer'  => 'LA PERSONA SELECCIONADA NO ES VÁLIDA.',
            'cod_personas_no_videntes_pca.exists'   => 'LA PERSONA SELECCIONADA NO EXISTE O FUE ELIMINADA.',
            'cod_capacitaciones_pca.required'       => 'DEBE INDICAR LA CAPACITACIÓN.',
            'cod_capacitaciones_pca.integer'        => 'LA CAPACITACIÓN INDICADA NO ES VÁLIDA.',
            'cod_capacitaciones_pca.exists'         => 'LA CAPACITACIÓN INDICADA NO EXISTE O FUE ELIMINADA.',
            'fei_pca.required'                      => 'LA FECHA DE INSCRIPCIÓN ES OBLIGATORIA.',
            'fei_pca.date'                          => 'LA FECHA DE INSCRIPCIÓN DEBE SER UNA FECHA VÁLIDA.',
            'fei_pca.date_format'                   => 'LA FECHA DE INSCRIPCIÓN DEBE TENER FORMATO YYYY-MM-DD.',
            'est_pca.required'                      => 'EL ESTADO DE LA PARTICIPACIÓN ES OBLIGATORIO.',
            'est_pca.in'                            => 'EL ESTADO SELECCIONADO NO ES VÁLIDO.',
            'obs_pca.max'                           => 'LAS OBSERVACIONES NO PUEDEN EXCEDER 500 CARACTERES.',
        ];
    }
}
