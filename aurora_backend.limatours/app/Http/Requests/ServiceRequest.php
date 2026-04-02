<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ServiceRequest extends FormRequest
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
        $rules = [
            'lang' => 'string',
            'client_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'quantity_persons' => 'required|array',
            'quantity_persons.adults' => 'required|integer|min:1',
            'quantity_persons.child' => 'required|integer|min:0',
            'quantity_persons.age_childs' => 'required|array',
            'limit' => 'integer',
            'recommendations' => 'boolean',
            'page' => 'integer',
        ];

        if (Request::input('type') != 'all') {
            $rules['type'] = 'array';
            $rules['type.*'] = 'integer';
        }

        if (Request::input('category') != 'all') {
            $rules['category'] = 'array';
            $rules['category.*'] = 'integer';
        }

        if (Request::input('experience') != 'all') {
            $rules['experience'] = 'array';
            $rules['experience.*'] = 'integer';
        }

        if (Request::input('classification') != 'all') {
            $rules['classification'] = 'array';
            $rules['classification.*'] = 'integer';
        }

        return $rules;
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'date.required' => 'Date is required.',
            'date.date_format' => 'the date format must be Y-m-d',
            //quantity_persons
            'quantity_persons.array' => 'Quantity must be an array',
            'quantity_persons.required' => 'Quantity persons is required.',
            'quantity_persons.adults.integer' => 'Adults must be an integer',
            'quantity_persons.adults.min' => 'The amount of adult must be minimum 1',
            'quantity_persons.child.integer' => 'Child must be an integer',
            'quantity_persons.child.min' => 'The amount of child must be minimum 0',
            'quantity_persons.age_childs.array' => 'Age childs must be an array',
            //Client
            'client_id.required' => 'Client ID is required.',
            'client_id.integer' => 'Client ID must be an integer',
            //Category
            'category.array' => 'Category must be an array',
            'category.*.integer' => 'The category must be an integer',
            //Experience
            'experience.array' => 'Experience must be an array',
            'experience.*.integer' => 'The Experience must be an integer',
            //type
            'type.array' => 'Type must be an array',
            'type.*.integer' => 'The type must be an integer',
            //classification
            'classification.array' => 'Classification must be an array',
            'classification.*.integer' => 'The classification must be an integer',
        ];
    }
}
