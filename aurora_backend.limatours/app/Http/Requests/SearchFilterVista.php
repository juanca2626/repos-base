<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchFilterVista extends FormRequest
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
            'client_id' => 'required|integer',
            'lang' => 'required|string',
            'filter' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'lang.required' => 'Lang is required.',
            'lang.string' => 'Lang must be an string.',
            'filter.required' => 'Filter is required.',
            'filter.string' => 'Filter must be an string.',
            'client_id.required' => 'Client ID is required.',
            'client_id.integer' => 'Client ID must be an integer',
        ];
    }
}
