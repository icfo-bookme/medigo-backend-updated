<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class EmployeeSalaryPaymenEdittFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {
        $this->rules['paid_amount']             = ['required', 'numeric', 'gte:0', 'lte:' . request()->due];

        $this->messages['paid_amount.required'] = 'This field is required';
        //
        $this->rules['employee_id']             = ['required', 'numeric', 'gt:0'];

        $this->messages['employee_id.required'] = 'This field is required';
        //
        $this->rules['payment_method']             = ['required', 'numeric', 'gt:0'];

        $this->messages['payment_method.required'] = 'This field is required';
        //
        $this->rules['account_id']             = ['required', 'numeric', 'gt:0'];

        $this->messages['account_id.required'] = 'This field is required';

        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
    }

    public function authorize()
    {
        return true;
    }
}
