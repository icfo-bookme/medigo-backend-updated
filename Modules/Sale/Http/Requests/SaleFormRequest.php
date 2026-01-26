<?php

namespace Modules\Sale\Http\Requests;

use App\Http\Requests\FormRequest;

class SaleFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['invoice_no']      = ['required'];
        $this->rules['sale_date']       = ['required','date','date_format:Y-m-d'];
        $this->rules['est_delivery_date']       = ['nullable'];
        $this->rules['customer_id']     = ['required'];
//        $this->rules['warehouse_id']     = ['required'];
        $this->rules['order_discount']  = ['nullable','numeric','gte:0'];
        $this->rules['shipping_cost']   = ['nullable','numeric','gte:0'];
        $this->rules['order_source_id']   = ['nullable'];

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {
                $this->rules['products.'.$key.'.qty']             = ['required','numeric','lte:'.$value['stock_qty']];
                $this->messages['products.'.$key.'.qty.required'] = 'This field is required';
                $this->messages['products.'.$key.'.qty.numeric']  = 'The value must be numeric';
//                $this->messages['products.'.$key.'.qty.gt']       = 'The value must be greater than 0';
                $this->rules['products.'.$key.'.net_unit_price']             = ['required','numeric','gt:0'];
                $this->messages['products.'.$key.'.net_unit_price.required'] = 'This field is required';
                $this->messages['products.'.$key.'.net_unit_price.numeric']  = 'The value must be numeric';
//                $this->messages['products.'.$key.'.net_unit_price.gt']       = 'The value must be greater than 0';
            }
        }


//        $this->rules['paid_amount'] = ['required','numeric','gte:0'.request()->net_total];
//        $this->rules['payment_method'] = ['required'];
//        $this->rules['account_id'] = ['required'];

        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
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
