<?php

namespace Modules\StockReturn\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockReturnRequest extends FormRequest
{
    protected $rules;
    protected $messages = [];

    public function rules()
    {
        $this->rules['invoice_no'] = ['required'];
        $this->rules['sale_date'] = ['required', 'date', 'date_format:Y-m-d'];
        $this->rules['return_date'] = ['required', 'date', 'date_format:Y-m-d'];
        $this->rules['customer_id'] = ['required'];

        if (request()->has('products')) {
            foreach (request()->products as $key => $value) {
                if ($value['return'] == 1) {
                    $this->rules['products.' . $key . '.return_qty'] = ['required', 'numeric', 'gt:0', 'lte:' . $value['sold_qty']];
                    $this->rules['products.' . $key . '.deduction_rate'] = ['numeric'];

                    $this->messages['products.' . $key . '.return_qty.required'] = 'This field is required';
                    $this->messages['products.' . $key . '.return_qty.numeric'] = 'The value must be numeric';
                    $this->messages['products.' . $key . '.return_qty.gt'] = 'The value must be greater than 0';
                    $this->messages['products.' . $key . '.deduction_rate.numeric'] = 'The value must be numeric';
                }
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
