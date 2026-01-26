<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class OvertimeFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['employee_id']    = ['required', 'numeric'];
        $rules['start_date']        = ['required'];
        $rules['end_date']           = ['required', 'after_or_equal:start_date'];
        $rules['start_time']        = ['required'];
        $rules['end_time']        = ['required'];
        $rules['working_hour']           = ['required', 'numeric', 'gt:0'];
        $rules['approve_remarks']            = ['nullable'];
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
