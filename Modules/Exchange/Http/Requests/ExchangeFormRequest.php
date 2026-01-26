<?php

namespace Modules\Exchange\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExchangeFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;
    public function rules()
    {
        $this->rules = [
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_name' => 'required|string|max:255',
            'return_date' => 'required|date',
            'exchange_qty' => 'required|numeric|min:1',
            'total_qty' => 'required|numeric|min:1',
            'net_total' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_amount' => 'nullable|numeric|min:0',
        ];

        if (request()->has('old_sale')) {
            foreach (request()->old_sale as $key => $value) {
                $this->rules['old_sale.' . $key . '.old_product_id'] = 'required|exists:products,id';
                $this->rules['old_sale.' . $key . '.old_product_code'] = 'required|string|max:100';
                $this->rules['old_sale.' . $key . '.old_stock_qty'] = 'required|numeric|min:1';
                $this->rules['old_sale.' . $key . '.old_price'] = 'required|numeric|min:0';
                $this->rules['old_sale.' . $key . '.old_exchange_qty'] = 'required|numeric|min:1';
            }
        }

        if (request()->has('new_sale')) {
            foreach (request()->new_sale as $key => $value) {
                $this->rules['new_sale.' . $key . '.product_id'] = 'required|exists:products,id';
                $this->rules['new_sale.' . $key . '.product_code'] = 'required|string|max:100';
                $this->rules['new_sale.' . $key . '.price'] = 'required|numeric|min:0';
                $this->rules['new_sale.' . $key . '.exchange_qty'] = 'required|numeric|min:1';
            }
        }

        if (request()->has('payment')) {
            foreach (request()->payment as $key => $value) {
                $this->rules['payment.' . $key . '.payment_method'] = 'required';
                $this->rules['payment.' . $key . '.account_id'] = 'required';
                $this->rules['payment.' . $key . '.payment_amount'] = 'required|numeric|gt:0';
            }
        }
        return $this->rules;
    }

    public function messages()
    {
        $this->messages = [
            'customer_id.required' => 'Customer is required',
            'customer_id.exists' => 'Customer is invalid',
            'warehouse_id.required' => 'Warehouse is required',
            'warehouse_id.exists' => 'Warehouse is invalid',
            'customer_name.required' => 'Customer name is required',
            'customer_name.string' => 'Customer name must be string',
            'customer_name.max' => 'Customer name must be maximum 255 characters',
            'return_date.required' => 'Return date is required',
            'return_date.date' => 'Return date must be date',
            'exchange_qty.required' => 'Exchange qty is required',
            'exchange_qty.numeric' => 'Exchange qty must be numeric',
            'exchange_qty.min' => 'Exchange qty must be minimum 1',
            'total_qty.required' => 'Total qty is required',
            'total_qty.numeric' => 'Total qty must be numeric',
            'total_qty.min' => 'Total qty must be minimum 1',
            'net_total.required' => 'Net total is required',
            'net_total.numeric' => 'Net total must be numeric',
            'net_total.min' => 'Net total must be minimum 0',
            'grand_total.required' => 'Grand total is required',
            'grand_total.numeric' => 'Grand total must be numeric',
            'grand_total.min' => 'Grand total must be minimum 0',
            'paid_amount.numeric' => 'Paid amount must be numeric',
            'paid_amount.min' => 'Paid amount must be minimum 0',
            'due_amount.numeric' => 'Due amount must be numeric',
            'due_amount.min' => 'Due amount must be minimum 0',
        ];

        if (request()->has('old_sale')) {
            foreach (request()->old_sale as $key => $value) {
                $this->messages['old_sale.' . $key . '.old_product_id.required'] = 'Old product ID is required for each old sale item.';
                $this->messages['old_sale.' . $key . '.old_product_code.required'] = 'Old product code is required for each old sale item.';
                $this->messages['old_sale.' . $key . '.old_stock_qty.required'] = 'Old stock quantity is required for each old sale item.';
                $this->messages['old_sale.' . $key . '.old_price.required'] = 'Old price is required for each old sale item.';
                $this->messages['old_sale.' . $key . '.old_exchange_qty.required'] = 'Old exchange quantity is required for each old sale item.';
            }
        }

        if (request()->has('new_sale')) {
            foreach (request()->new_sale as $key => $value) {
                $this->messages['new_sale.' . $key . '.product_id.required'] = 'Product ID is required for each new sale item.';
                $this->messages['new_sale.' . $key . '.product_code.required'] = 'Product code is required for each new sale item.';
                $this->messages['new_sale.' . $key . '.price.required'] = 'Price is required for each new sale item.';
                $this->messages['new_sale.' . $key . '.exchange_qty.required'] = 'Exchange quantity is required for each new sale item.';
            }
        }

        if (request()->has('payment')) {
            foreach (request()->payment as $key => $value) {
                $this->messages['payment.' . $key . '.payment_method.required'] = 'Payment method is required for each payment item.';
                $this->messages['payment.' . $key . '.account_id.required'] = 'Account is required for each payment item.';
                $this->messages['payment.' . $key . '.paid_amount.required'] = 'Paid amount is required for each payment item.';
                $this->messages['payment.' . $key . '.paid_amount.numeric'] = 'Paid amount must be numeric for each payment item.';
                $this->messages['payment.' . $key . '.paid_amount.gt'] = 'Paid amount must be greater than 0 for each payment item.';
            }
        }
        return $this->messages;
    }

    public function authorize()
    {
        return true;
    }
}
