<?php

namespace Src\Modules\File\Presentation\Http\Requests\FileItineraryFlight;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateFileItineraryFlightCityIsoRequest extends FormRequest {
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
            'type_flight'               => 'required|string',
            'city_iso'                  => 'required|string',
            'city_name'                 => 'required|string',
            'country_iso'               => 'required|string',
            'country_name'              => 'required|string'
        ];
    }

    public function messages()
    {
        return [

        ];
    }
}
