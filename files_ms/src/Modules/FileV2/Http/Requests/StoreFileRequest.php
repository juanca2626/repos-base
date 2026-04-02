<?php

namespace Src\Modules\FileV2\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file_code' => 'required|string',
            'client_id' => 'required|integer',
            'client_code' => 'required|string',
            // 'executive_id' => 'required|integer',
            // 'executive_code' => 'required|string',

            'reservations_passenger' => 'required|array|min:1',
            // 'reservations_passenger.*.name' => 'required|string',
            // 'passengers.*.document_type_id' => 'required|integer',

            //'categories' => 'nullable|array',
            //'categories.*.category_id' => 'required|integer',
        ];
    }
}
