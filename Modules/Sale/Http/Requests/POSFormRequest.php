<?php

namespace Modules\Sale\Http\Requests;

use App\Http\Requests\FormRequest;

class POSFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;

    public function rules()
    {
        $this->rules['invoice_no']      = ['required'];
        //$this->rules['sale_date']       = ['required','date','date_format:Y-m-d'];
        // $this->rules['sale_type']       = ['required'];
        $this->rules['customer_id']     = ['required'];
        //$this->rules['delivery_status'] = ['required'];
        // $this->rules['delivery_date']   = ['required','date','date_format:Y-m-d'];
        $this->rules['order_discount']  = ['nullable','numeric','gte:0'];
        $this->rules['shipping_cost']   = ['nullable','numeric','gte:0'];
        $this->rules['order_source_id']   = ['nullable'];

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {
                $this->rules['products.'.$key.'.qty']             = ['required','numeric','gt:0','lte:'.$value['stock_qty']];
                $this->messages['products.'.$key.'.qty.required'] = 'This field is required';
                $this->messages['products.'.$key.'.qty.numeric']  = 'The value must be numeric';
                $this->messages['products.'.$key.'.qty.gt']       = 'The value must be greater than 0';
                $this->rules['products.'.$key.'.net_unit_price']             = ['required','numeric','gt:0'];
                $this->messages['products.'.$key.'.net_unit_price.required'] = 'This field is required';
                $this->messages['products.'.$key.'.net_unit_price.numeric']  = 'The value must be numeric';
                $this->messages['products.'.$key.'.net_unit_price.gt']       = 'The value must be greater than 0';
            }
        }

//        if(empty(request()->sale_id))
//        {
//            $this->rules['payment_status'] = ['required'];
//            if(!empty(request()->payment_status) && request()->payment_status != 3)
//            {
//                $this->rules['paid_amount'] = ['required','numeric','gt:0','lt:'.request()->net_total];
//                if(request()->payment_status == 1)
//                {
//                    $this->rules['paid_amount'][2] = 'required';
//                    $this->rules['paid_amount'][3] = 'required';
//                }
//                $this->rules['payment_method'] = ['required'];
//                $this->rules['account_id'] = ['required'];
//            }
//        }


        if (request()->has('payment') && request()->payment_status != 3) {
            foreach (request()->payment as $key => $value) {

                $this->rules['payment.' . $key . '.payment_method'] = ['required'];
                $this->rules['payment.' . $key . '.account_id'] = ['required'];
                $this->rules['payment.' . $key . '.payment_amount'] = ['required','gt:0'];


                $this->messages['payment.' . $key . '.payment_method.required'] = 'This field is required';
                $this->messages['payment.' . $key . '.account_id.required'] = 'This field is required';
                $this->messages['payment.' . $key . '.payment_amount.required'] ='This field is required';
                $this->messages['payment.' . $key . '.payment_amount.gt'] = 'The value must be greater than 0';

            }
        }


        $this->rules['paid_amount'] = ['nullable', 'numeric'];
        $this->rules['due_amount'] = ['required'];


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
