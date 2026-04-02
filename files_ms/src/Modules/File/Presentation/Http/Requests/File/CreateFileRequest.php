<?php

namespace Src\Modules\File\Presentation\Http\Requests\File;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateFileRequest extends FormRequest
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
        $ruleRequitedNumeric = 'required|numeric';
        return [
            'id' => $ruleRequitedNumeric,
            'client_id' => $ruleRequitedNumeric,
            'file_code' => $ruleRequitedNumeric,
            'reservation_number' => 'nullable|string',
            'budget_number' => 'nullable|string|max:45',
            'lang' => 'string|max:2',
            'date_init' => 'required|date'
        ];
    }
}
