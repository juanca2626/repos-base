<?php

namespace Src\Modules\File\Presentation\Http\Requests\FileItineraryFlight;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateFileItineraryFlightRequest extends FormRequest
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
            'airline_code' => 'string|nullable|min:0|max:2',
            'airline_number' => 'string|nullable|max:10',
            'pnr' => 'string|nullable|max:20',            
            'nro_pax' => 'required|numeric|max:200',
        ];
    }
}
