<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class PackageRequest extends FormRequest
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
            'package_id' => 'integer',
            'date' => 'required|date_format:Y-m-d|after_or_equal:'.date('Y-m-d'),
            'quantity_persons' => 'required|array',
            'type_package' => 'required|integer',
            'quantity_persons.adults' => 'required|integer|min:1',
            'quantity_persons.child' => 'required|integer|min:0',
            'quantity_persons.age_child' => 'required|array',
            'recommendations' => 'boolean',
            'type_service' => 'integer'
        ];

        if (Request::input('category') != 'all') {
            $rules['category'] = 'integer';
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
            'date.after_or_equal' => 'the date must be greater than or equal to the current date',
            //quantity_persons
            'quantity_persons.array' => 'Quantity must be an array',
            'quantity_persons.required' => 'Quantity persons is required.',
            'quantity_persons.adults.integer' => 'Adults must be an integer',
            'quantity_persons.adults.min' => 'The amount of adult must be minimum 1',
            'quantity_persons.child.integer' => 'Child must be an integer',
            'quantity_persons.child.min' => 'The amount of child must be minimum 0',
            'quantity_persons.age_child.array' => 'Age childs must be an array',
            'type_package.required' => 'Type Package is required.',
            'type_package.integer' => 'category must be an integer [0 => Package, 1=> Extensions, 2=> Exclusives]',
            //Client
            'client_id.required' => 'Client ID is required.',
            'client_id.integer' => 'Client ID must be an integer',
//            'type_service.required' => 'Type of service is required [0 => shared, 1 => private]',
            'type_service.integer' => 'Type of service must be an integer',
            'category.integer' => 'category must be an integer',
            'package_id.integer' => 'Package ID must be an integer',
        ];


    }
}
