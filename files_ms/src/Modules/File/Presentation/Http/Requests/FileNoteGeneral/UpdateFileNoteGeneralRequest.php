<?php

namespace Src\Modules\File\Presentation\Http\Requests\FileNoteGeneral;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateFileNoteGeneralRequest extends FormRequest
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
            'date_event'             => 'nullable|date',
            'type_event'             => 'nullable|string',
            'description_event'      => 'nullable|string',
            'image_logo'             => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [

        ];
    }
}
