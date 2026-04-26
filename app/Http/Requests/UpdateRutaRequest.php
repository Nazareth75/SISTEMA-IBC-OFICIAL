<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRutaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeEditarRuta() ?? false;
    }

    public function rules(): array
    {
        return [
            'nom_rut' => 'required|string|max:150',
            'des_rut' => 'nullable|string|max:300',
            'ori_rut' => 'required|string|max:200',
            'dst_rut' => 'required|string|max:200',
            'geo_rut' => 'nullable|json',
            'dis_rut' => 'nullable|numeric|min:0|max:99999.99',
            'tim_rut' => 'nullable|integer|min:1|max:9999',
            'obs_rut' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nom_rut.required' => 'EL NOMBRE DE LA RUTA ES OBLIGATORIO.',
            'nom_rut.max'      => 'EL NOMBRE NO PUEDE EXCEDER 150 CARACTERES.',
            'des_rut.max'      => 'LA DESCRIPCIÓN NO PUEDE EXCEDER 300 CARACTERES.',
            'ori_rut.required' => 'EL ORIGEN ES OBLIGATORIO.',
            'ori_rut.max'      => 'EL ORIGEN NO PUEDE EXCEDER 200 CARACTERES.',
            'dst_rut.required' => 'EL DESTINO ES OBLIGATORIO.',
            'dst_rut.max'      => 'EL DESTINO NO PUEDE EXCEDER 200 CARACTERES.',
            'geo_rut.json'     => 'LA GEOMETRÍA DE LA RUTA NO TIENE UN FORMATO VÁLIDO.',
            'dis_rut.numeric'  => 'LA DISTANCIA DEBE SER UN NÚMERO VÁLIDO.',
            'dis_rut.min'      => 'LA DISTANCIA NO PUEDE SER NEGATIVA.',
            'tim_rut.integer'  => 'EL TIEMPO ESTIMADO DEBE SER UN NÚMERO ENTERO.',
            'tim_rut.min'      => 'EL TIEMPO ESTIMADO DEBE SER AL MENOS 1 MINUTO.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'nom_rut' => $this->nom_rut ? strtoupper(trim($this->nom_rut)) : null,
            'ori_rut' => $this->ori_rut ? strtoupper(trim($this->ori_rut)) : null,
            'dst_rut' => $this->dst_rut ? strtoupper(trim($this->dst_rut)) : null,
        ]);
    }
}
