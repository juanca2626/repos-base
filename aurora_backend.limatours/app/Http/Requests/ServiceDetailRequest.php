<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ServiceDetailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lang' => 'required|string',
            'id' => 'required|array|min:1',
            'id.*' => 'integer',
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'lang.required' => 'Language is required.',
            'id.required' => 'Id or Ids is required.',
            'id.array' => 'Id must be an array',
            'id.min' => 'The id must be minimum 1',
            'id.*.integer' => 'The Id must be an integer',
        ];
    }
}
