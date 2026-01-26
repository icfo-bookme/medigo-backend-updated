<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class LabourSalaryPaymentFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['payment_type']  = ['required'];
        $rules['note']          = ['required'];

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
