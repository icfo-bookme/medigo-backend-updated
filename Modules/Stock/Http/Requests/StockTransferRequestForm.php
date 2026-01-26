<?php

namespace Modules\Stock\Http\Requests;

use App\Http\Requests\FormRequest;

class StockTransferRequestForm extends FormRequest{
    protected $rules;
    protected $messages;
    public function rules(){
        $this->rules['transfer_date']                  = ['required'];
        $this->messages['transfer_date']               = 'This Field Is Required';
        $this->rules['transfer_warehouse_id']          = ['required'];
        $this->messages['transfer_warehouse_id']       = 'This Field Is Required';
        $this->rules['receive_warehouse_id']           = ['required'];
        $this->messages['receive_warehouse_id']        = 'This Field Is Required';
        return $this->rules;
    }
    public function authorize(){
        return true;
    }
    public function messages(){
        return $this->messages;
    }
}
