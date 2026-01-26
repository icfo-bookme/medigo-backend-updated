<?php

namespace Modules\Coupon\Http\Requests;

use App\Http\Requests\FormRequest;

class CouponRequest extends FormRequest
{
    protected $rules = [];

    public function rules()
    {
    
           $rules['coupon_type'] = 'required';
           $rules['name'] = 'required';
           $rules['type'] = 'required';
           $rules['value'] = 'required';
           $rules['discount_amount'] = 'nullable';
           $rules['coupon_value_limit'] = 'required';
           $rules['start_date'] = 'required';
           $rules['end_date'] = 'required';
 
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
