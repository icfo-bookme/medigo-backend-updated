<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class SalaryGenerateFormRequest extends FormRequest
{
    public function rules()
    {
        $rules['month_id'] = ['required'];
        $rules['year_id'] = ['required'];

        if (request()->has('salary')) {
            foreach (request()->salary as $key => $value) {
                $rules['salary.' . $key . '.shift_id'] = ['required', 'numeric', 'gt:0'];
                $rules['salary.' . $key . '.employee_id'] = ['required', 'numeric', 'gt:0'];
                $rules['salary.' . $key . '.total_working_days'] = ['required', 'numeric', 'gt:0'];
                $rules['salary.' . $key . '.total_holidays'] = ['required', 'numeric', 'gte:0'];
                $rules['salary.' . $key . '.total_attended'] = ['required', 'numeric', 'gte:0'];
                $rules['salary.' . $key . '.total_paid_leaves'] = ['required', 'numeric', 'gte:0'];
                $rules['salary.' . $key . '.total_unpaid_leaves'] = ['required', 'numeric', 'gte:0'];
                $rules['salary.' . $key . '.total_working_hours'] = ['required', 'numeric', 'gte:0'];
                $rules['salary.' . $key . '.current_salary'] = ['required', 'numeric', 'gte:0'];
                $rules['salary.' . $key . '.increment_salary'] = ['required', 'numeric', 'gte:0'];
                $rules['salary.' . $key . '.gross_salary'] = ['required', 'numeric', 'gte:0'];
                $rules['salary.' . $key . '.net_salary'] = ['required', 'numeric', 'gte:0'];
            }
        }
        return $rules;
    }


    public function authorize()
    {
        return true;
    }
}
