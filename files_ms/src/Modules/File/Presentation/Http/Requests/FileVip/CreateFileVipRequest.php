<?php

namespace Src\Modules\File\Presentation\Http\Requests\FileVip;

use Illuminate\Foundation\Http\FormRequest;

class CreateFileVipRequest extends FormRequest
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
            // 'file_id' => 'required|numeric',
            'vip_id' => 'required|numeric',
        ];
    }
}
