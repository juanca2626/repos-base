<?php

namespace Src\Modules\File\Presentation\Http\Requests\File;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateFileToOpeRequest extends FormRequest
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
            'status' => 'required|numeric|in:1,2',
        ];
    }

    public function messages()
    {
        return [
            'status' => 'The status is invalid,  1 and 2 are allowed', 
        ];
    }
}
