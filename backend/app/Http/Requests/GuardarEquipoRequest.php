<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarEquipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
       
        $equipo = $this->route('equipo');

        return [
            'codigo'            => [
                'required', 'string', 'max:20', 'regex:/^EQ-\d{4}$/',
                Rule::unique('equipos', 'codigo')->ignore($equipo),
            ],
            'nombre'            => ['required', 'string', 'between:3,120'],
            'categoria_id'      => ['required', 'integer', 'exists:categorias,id'],
            'estado'            => ['sometimes', 'nullable', Rule::in(['disponible', 'mantenimiento'])],
            'fecha_adquisicion' => ['required', 'date', 'before_or_equal:today'],
            'costo_interno'     => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El codigo es obligatorio.',
            'codigo.string'   => 'El codigo debe ser texto.',
            'codigo.max'      => 'El codigo no puede superar los 20 caracteres.',
            'codigo.regex'    => 'El codigo debe tener el formato EQ-0000.',
            'codigo.unique'   => 'Ya existe un equipo con ese codigo.',

            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string'   => 'El nombre debe ser texto.',
            'nombre.between'  => 'El nombre debe tener entre 3 y 120 caracteres.',

            'categoria_id.required' => 'La categoria es obligatoria.',
            'categoria_id.integer'  => 'La categoria debe ser un numero entero.',
            'categoria_id.exists'   => 'La categoria seleccionada no existe.',

            'estado.in' => 'El estado solo puede ser disponible o mantenimiento.',

            'fecha_adquisicion.required'        => 'La fecha de adquisicion es obligatoria.',
            'fecha_adquisicion.date'            => 'La fecha de adquisicion no es una fecha valida.',
            'fecha_adquisicion.before_or_equal' => 'La fecha de adquisicion no puede ser futura.',

            'costo_interno.required' => 'El costo es obligatorio.',
            'costo_interno.numeric'  => 'El costo debe ser numerico.',
            'costo_interno.min'      => 'El costo debe ser mayor o igual a 0.',
        ];
    }
}
