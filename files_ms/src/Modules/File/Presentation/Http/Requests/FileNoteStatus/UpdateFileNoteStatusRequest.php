<?php

namespace Src\Modules\File\Presentation\Http\Requests\FileNoteStatus;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateFileNoteStatusRequest extends FormRequest{
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
            'entity'            => 'required|string|in:mask,note',
            'status'            => 'required|string|in:received,refused,approved',
            'comment'           => 'required|string|max:500',
            'created_by'        => 'required|integer',
            'created_by_code'   => 'required',
            'created_by_name'   => 'required|string',
        ];
    }

    public function messages()
    {
         return [
            'entity.in' => 'The accepted values ​​for entity are: mask, note',
            'status.in' => 'The accepted values ​​for status are: received, refused, approved',
            'comment.max' => 'The comment must not exceed 500 characters.',
        ];
    }
}
