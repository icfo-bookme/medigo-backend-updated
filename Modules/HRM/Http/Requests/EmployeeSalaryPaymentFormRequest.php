<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class EmployeeSalaryPaymentFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {
        if(request()->has('salary'))
        {
            foreach (request()->salary as $key => $value) {
                $this->rules['salary.'.$key.'.paid_amount']             = ['required','numeric','gte:0','lte:'.$value['due']];

                $this->messages['salary.'.$key.'.paid_amount.required'] = 'This field is required';
                //
                $this->rules['salary.'.$key.'.employee_id']             = ['required','numeric','gt:0'];

                $this->messages['salary.'.$key.'.employee_id.required'] = 'This field is required';
                //
                $this->rules['salary.'.$key.'.payment_method']             = ['required','numeric','gt:0'];

                $this->messages['salary.'.$key.'.payment_method.required'] = 'This field is required';
                //
                $this->rules['salary.'.$key.'.account_id']             = ['required','numeric','gt:0'];

                $this->messages['salary.'.$key.'.account_id.required'] = 'This field is required';
            }
        }

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
