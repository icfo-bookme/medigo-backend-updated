<?php

namespace Modules\Sale\Http\Requests;


use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

class OrderPackageRequestForm extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {
        $this->rules['update_id']      = ['nullable'];
//        if(empty($this->request->update_id)){
//            $this->rules['name']    = ['required',    Rule::unique('order_packages')->where(function ($query) {
//                $query->where('name', request('name'))
//                    ->where('user_id',auth()->user()->id);
//            })->ignore(request('update_id'), 'id')];
//        }else{
//            $this->rules['name']    = ['required'];
//        }

        $this->rules['name'] = [
            'required',
            Rule::unique('order_packages')->where(function ($query) {
                $query->where('name', request('name'))
                    ->where('user_id', auth()->user()->id);
            })->ignore(request('update_id'), 'id') // Use 'id' as the second argument
        ];



        $this->rules['start_date']    = ['required'];
        $this->rules['delivery_date']    = ['nullable'];
        $this->rules['auto_order_after_days']    = ['nullable'];
        $this->rules['grand_total']    = ['nullable'];
        $this->rules['item']    = ['nullable'];
        $this->rules['total_discount']    = ['nullable'];
        $this->rules['shipping_cost']    = ['nullable'];

        $collection = collect(request());

        if ($collection->has('products')) {
            foreach (request()->products as $idx => $item) {

                $this->rules["products.{$idx}.id"] = ['nullable'];
                $this->rules["products.{$idx}.product_id"] = ['required'];
                $this->rules["products.{$idx}.net_unit_price"] = ['nullable'];
                $this->rules["products.{$idx}.qty"] = ['required'];
                $this->rules["products.{$idx}.sale_unit_id"] = ['required'];
                $this->rules["products.{$idx}.discount"] = ['required'];
                $this->rules["products.{$idx}.discount_rate"] = ['required'];
                $this->rules["products.{$idx}.total"] = ['required'];

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
