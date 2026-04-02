<?php

namespace Src\Modules\File\Presentation\Http\Requests\File;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateStatusReopenedFileRequest extends FormRequest
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
            'status_reason_id' => 'required|numeric|in:2,3,4',
        ];
    }

    public function messages()
    {
        return [
            'status_reason_id' => 'The status_reason_id is invalid: 2,3,4 are allowed', 
        ];
    }

}
