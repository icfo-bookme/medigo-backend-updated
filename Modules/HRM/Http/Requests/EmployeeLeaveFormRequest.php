<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class EmployeeLeaveFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['employee_id']    = ['required', 'numeric'];
        $rules['leave_category_id']       = ['required', 'numeric'];
        $rules['start_date']        = ['required'];
        $rules['end_date']           = ['required', 'after_or_equal:start_date'];
        $rules['duration']           = ['required', 'numeric', 'gt:0'];
        $rules['notes']            = ['nullable'];
        $rules['is_paid']            = ['required'];
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
