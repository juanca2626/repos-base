<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetUsersDetailsRequest extends FormRequest
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
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
            'per_page' => 'integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_ids.required' => 'Se requiere una lista de IDs de usuario.',
            'user_ids.array' => 'Los IDs de usuario deben ser proporcionados como un array.',
            'user_ids.*.integer' => 'Cada ID de usuario debe ser un número entero.',
            'user_ids.*.exists' => 'Uno o más IDs de usuario no existen en nuestra base de datos.',
            'per_page.integer' => 'El número de resultados por página debe ser un número entero.',
            'per_page.min' => 'El número de resultados por página debe ser al menos 1.',
            'per_page.max' => 'El número de resultados por página no puede exceder 100.',
        ];
    }
}
