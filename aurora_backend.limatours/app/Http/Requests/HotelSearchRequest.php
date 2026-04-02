<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Si se envía, hotels_id debe ser un array (puede ser nulo)
            'hotels_id'           => 'sometimes|nullable|array',
            // hotels_search_code puede ser string o array (aquí lo definimos como string)
            // hotels_search_code puede ser un array o un string
            'hotels_search_code' => [
                'sometimes',
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!is_array($value) && !is_string($value)) {
                        $fail("The {$attribute} must be either a string or an array.");
                    }
                },
            ],
            // destiny debe ser un array si se envía
            'destiny'             => 'sometimes|nullable|array',
            // Si no se envía hotels_id ni hotels_search_code, se requiere que destiny.code esté presente
            'destiny.code'        => 'required_without_all:hotels_id,hotels_search_code',
        ];
    }

    public function messages()
    {
        return [
            // Mensaje para cuando destiny.code no está presente y tampoco se envían los otros dos campos
            'destiny.code.required_without_all' => 'Required fields are missing',
        ];
    }

    /**
     * Además, se agrega una validación personalizada que comprueba que, si no se envía
     * destiny.code (o destiny está vacío), al menos se envíe hotels_id o hotels_search_code.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $destiny = $this->input('destiny');
            $hotels_id = $this->input('hotels_id');
            $hotels_search_code = $this->input('hotels_search_code');

            // Usamos data_get para obtener destiny.code sin que se genere error si destiny es nulo
            if ((empty($destiny) || empty(data_get($destiny, 'code')))
                && empty($hotels_id)
                && empty($hotels_search_code)
            ) {
                $validator->errors()->add('hotels_id', 'hotels_id or hotels_search_code is required');
            }
        });
    }
}
