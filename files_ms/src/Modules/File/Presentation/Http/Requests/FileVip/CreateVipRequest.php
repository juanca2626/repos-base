<?php

namespace Src\Modules\File\Presentation\Http\Requests\FileVip;

use Illuminate\Foundation\Http\FormRequest;

class CreateVipRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'entity' => 'required|string',
            'name' => 'required|string|unique:vips',
        ];
    }
}
