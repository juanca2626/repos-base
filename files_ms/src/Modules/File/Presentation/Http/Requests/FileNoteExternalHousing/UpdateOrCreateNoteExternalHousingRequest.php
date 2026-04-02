<?php

namespace Src\Modules\File\Presentation\Http\Requests\FileNoteExternalHousing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateOrCreateNoteExternalHousingRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(Request $request): array {
        return [
            'date_check_in' => 'required|date_format:Y-m-d',
            'date_check_out' => 'required|date_format:Y-m-d|after_or_equal:date_check_in',
            'passengers' => [
                'required',
                'array'
            ],
            'name_housing'      => 'required|string',
            'code_phone'        => 'required',
            'number_phone'      => 'required',
            'address'           => 'required',
            'lat'               => 'required',
            'lng'               => 'required',
            'city'              => 'required',
            'created_by'         => 'nullable',
            'created_by_code'    => 'nullable',
            'created_by_name'    => 'nullable',
        ];
    }

    public function messages()
    {
       return [
            'date_check_in.required'         => 'La fecha de date-check-in es obligatoria.',
            'date_check_in.date_format'      => 'El formato de date-check-in debe ser YYYY-MM-DD.',
            'date_check_out.required'        => 'La fecha de date-check-out es obligatoria.',
            'date_check_out.date_format'     => 'El formato de date-check-out debe ser YYYY-MM-DD.',
            'date_check_out.after_or_equal'  => 'El date-check-out debe ser igual o posterior al date-check-in.'
        ];
    }

}
