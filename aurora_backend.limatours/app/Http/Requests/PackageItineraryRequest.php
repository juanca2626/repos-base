<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;

class PackageItineraryRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lang'         => 'required|string|in:es,en,pt',
            'client_id'    => 'required|integer|exists:clients,id',
            'category_id'  => 'integer|exists:type_classes,id',
            'package_id'   => 'required|integer|exists:packages,id',
            'use_cover'   => 'required|boolean',
            'url_cover'    => 'nullable|string',
            'use_prices'   => 'required|boolean',
            'year'         => 'integer',
            'token_search' => 'required|string',
        ];
    }

    /**
     * Agrega validaciones adicionales después de las reglas básicas.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $token = $this->input('token_search');

            // Verificamos que el token exista en la caché.
            if (!Cache::has($token)) {
                $validator->errors()->add('token_search', 'El token no existe en la caché.');
            } else {
                $data = Cache::get($token);
                $packageId = $this->input('package_id');

                // Convertimos el dato recuperado en una colección para facilitar la búsqueda.
                // Se valida que exista un elemento cuyo campo 'id' coincida con package_id.
                if (!collect($data)->contains('id', $packageId)) {
                    $validator->errors()->add('token_search', 'El package_id no se encuentra en el token_search.');
                }
            }
        });
    }
    /**
     * Mensajes personalizados para la validación.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'lang.required'         => 'El campo lang es obligatorio.',
            'lang.in'               => 'El campo lang debe ser uno de los siguientes valores: es, en, pt.',
            'client_id.required'    => 'El campo client_id es obligatorio.',
            'client_id.integer'     => 'El campo client_id debe ser un número entero.',
            'client_id.exists'      => 'El client_id no existe.',
            'category_id.required'  => 'El campo category_id es obligatorio.',
            'category_id.integer'   => 'El campo category_id debe ser un número entero.',
            'category_id.exists'    => 'El category_id no existe.',
            'package_id.required'   => 'El campo package_id es obligatorio.',
            'package_id.integer'    => 'El campo package_id debe ser un número entero.',
            'package_id.exists'     => 'El package_id no existe.',
            'use_header.required'   => 'El campo use_header es obligatorio.',
            'use_header.boolean'    => 'El campo use_header debe ser verdadero o falso.',
            'use_prices.required'   => 'El campo use_prices es obligatorio.',
            'use_prices.boolean'    => 'El campo use_prices debe ser verdadero o falso.',
            'token_search.required' => 'El campo token_search es obligatorio.',
            'token_search.string'   => 'El campo token_search debe ser una cadena de texto.',
            'year.required'         => 'El campo year es obligatorio.',
            'year.integer'          => 'El campo year debe ser un número entero.',
            'url_cover.string'      => 'El campo url_cover debe ser una cadena de texto.',
        ];
    }
}
