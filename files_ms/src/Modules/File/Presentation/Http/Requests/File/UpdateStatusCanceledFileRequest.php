<?php

namespace Src\Modules\File\Presentation\Http\Requests\File;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateStatusCanceledFileRequest extends FormRequest
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
 

    public function rules(Request $request): array
    {
 
        return [ 
            'status_reason_id' => 'required|numeric|in:6,7,8,9,10,11,12,13',
        ];
    }

    public function messages()
    {
        return [
            'status_reason_id' => 'The status_reason_id is invalid: 6,7,8,9,10,11,12,13 are allowed', 
        ];
    }

}
