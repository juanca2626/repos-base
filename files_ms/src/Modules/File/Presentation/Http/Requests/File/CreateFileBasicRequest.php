<?php

namespace Src\Modules\File\Presentation\Http\Requests\File;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateFileBasicRequest extends FormRequest
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
        $ruleRequitedNumeric = 'required|numeric|min:1';
        return [
            // 'id' => $ruleRequitedNumeric,
            'description' => 'string|min:3|max:45',
            'date_init' => 'required|date',
            'client_id' => $ruleRequitedNumeric,  
            'client_code' => 'required|string|max:20',    
            'client_name' => 'required|string|max:255',  
            'adults' => $ruleRequitedNumeric, 
            'children' => 'nullable|numeric',
            'infants' => 'nullable|numeric', 
            'accommodation_sgl' => 'nullable|numeric', 
            'accommodation_dbl' => 'nullable|numeric',
            'accommodation_tpl' => 'nullable|numeric',            
            'lang' => 'string|max:2',            
        ];
    }
}
