<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class SalaryFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['employee_id']                   = ['required','string','unique:employees,employee_id'];
        $rules['basic_salary']                  = ['required'];
        $rules['employee_type']                 = ['required'];
        $rules['shift_id']                 = ['required', 'numeric', 'gt:0'];
        $rules['gross_salary']                 = ['required', 'numeric', 'gt:0'];
        $rules['total_deduction']                 = ['required', 'numeric', 'gte:0'];
        $rules['net_salary']                 = ['required', 'numeric', 'gt:0'];
        $rules['overtime_rate']                 = ['required', 'numeric', 'gt:0'];
        if(request()->has('leave')) {
            foreach (request()->leave as $key => $value) {
                $rules['leave.'.$key.'.number_of_days']             = ['required','numeric','gte:0'];
            }
        }
        return $rules;
    }

    public function authorize()
    {
        return true;
    }
}
