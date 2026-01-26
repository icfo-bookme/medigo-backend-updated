<?php

namespace Modules\Sale\Http\Requests;


use App\Http\Requests\FormRequest;

class PrescriptionOrderRequestForm extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {
        $this->rules['update_id']      = ['nullable'];
        $this->rules['prescription_file']    = ['required'];
        $this->rules['old_prescription_file']    = ['nullable'];

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
