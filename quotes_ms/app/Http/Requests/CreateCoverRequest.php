<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateCoverRequest extends FormRequest
{
    public function authorize()
    {
        // Permitir la autorización, puedes ajustar según tus necesidades
        return true;
    }

    public function rules()
    {
        return [
            'package_id' => [
                'required',
                'integer',
                // Valida que el paquete exista y que tenga status = 1
                Rule::exists('packages', 'id')->where('status', 1),
            ],
            'client_id' => [
                'required',
                'integer',
                // Valida que el cliente exista
                'exists:clients,id',
            ],
            'lang' => [
                'required',
                Rule::in(['es', 'en', 'pt']),
            ],
        ];
    }

    public function messages()
    {
        return [
            'package_id.required' => 'El campo package_id es obligatorio.',
            'package_id.integer'  => 'El campo package_id debe ser un número entero.',
            'package_id.exists'   => 'El paquete no existe o no está activo.',
            'client_id.required'  => 'El campo client_id es obligatorio.',
            'client_id.integer'   => 'El campo client_id debe ser un número entero.',
            'client_id.exists'    => 'El cliente no existe.',
            'lang.required'       => 'El campo lang es obligatorio.',
            'lang.in'             => 'El campo lang debe ser es, en o pt.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors'  => $validator->errors(),
        ], 422));
    }
}
