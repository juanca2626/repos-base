<?php

namespace Src\Modules\File\Presentation\Http\Requests\FileNote;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateFileNoteRequest extends FormRequest
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
            'type_note'             => 'required|in:INFORMATIVE,REQUIREMENT', // INFORMATIVE|REQUIREMENT
            // FOR_FILE|FOR_DATE|EXTERNAL_HOUSING
            'record_type' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Si type_note es INFORMATIVE, record_type puede ser FOR_FILE, FOR_DATE o EXTERNAL_HOUSING
                    if ($this->type_note === 'INFORMATIVE' && !in_array($value, ['FOR_FILE', 'FOR_DATE', 'EXTERNAL_HOUSING'])) {
                        $fail('El campo record_type no es válido cuando type_note es INFORMATIVE.');
                    }
                    // Si type_note no es INFORMATIVE, record_type solo puede ser FOR_DATE
                    if ($this->type_note !== 'INFORMATIVE' && $value !== 'FOR_DATE') {
                        $fail('El campo record_type solo puede ser FOR_DATE cuando type_note no es INFORMATIVE.');
                    }
                },
            ],
            'assignment_mode'       => 'required|in:ALL_DAY,FOR_SERVICE', // ALL_DAY|FOR_SERVICE
            'dates'                 => [
                'nullable',
                'required_if:record_type,FOR_DATE',
                'required_if:type_note,REQUIREMENT',
                'json',
                function ($attribute, $value, $fail) {
                    // Valida que el JSON contenga un array de fechas válidas
                    $dates = json_decode($value, true);
                    if (!is_array($dates)) {
                        $fail('El campo dates debe ser un JSON con un array de fechas.');
                    }
                    foreach ($dates as $date) {
                        if (!strtotime($date)) {
                            $fail('El campo dates contiene fechas no válidas.');
                        }
                    }
                },
            ],
            'description'       => 'required',
            'classification_code' => [
                'nullable',
                'required_if:record_type,FOR_DATE',
                'required_if:type_note,REQUIREMENT',
            ],
            'classification_name' => [
                'nullable',
                'required_if:record_type,FOR_DATE',
                'required_if:type_note,REQUIREMENT',
            ],
            'created_by'         => ['nullable'],
            'created_by_code'    => ['nullable'],
            'created_by_name'    => ['nullable'],
            'service_ids'        => [
                'nullable',
                'required_if:record_type, FOR_SERVICE',
                'required_if:json, FOR_SERVICE'
            ],
        ];
    }

    public function messages()
    {
        return [
            'type_note.required'                    => 'El campo type_note es obligatorio.',
            'type_note.in'                          => 'El campo type_note no es válido.',
            'record_type.required'                  => 'El campo record_type es obligatorio.',
            'record_type.in'                        => 'El campo record_type no es válido.',
            'dates.required_if'                     => 'El campo dates es obligatorio cuando record_type es FOR_DATE o type_note es REQUIREMENT.',
            'dates.json'                            => 'El campo dates debe ser un JSON válido.',
            'description'                           => 'El campo description es obligatorio.',
            'classification_code.required_if'       => 'El campo classification_code es obligatorio.',
            'classification_name.required_if'       => 'El campo classification_name es obligatorio.',
            'service_ids.required_if'               => 'El campo service_ids es obligatorio.',
        ];
    }
}
