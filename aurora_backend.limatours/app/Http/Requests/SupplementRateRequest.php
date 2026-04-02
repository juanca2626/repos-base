<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplementRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'rate_plan_id'=>'required|exists:rates_plans,id',
            'supplement_id'=>'required|exists:hotel_supplements,supplement_id',
            'type'=>'required|in:required,optional',
            'amount_extra'=>'required|boolean'
        ];
    }
}
