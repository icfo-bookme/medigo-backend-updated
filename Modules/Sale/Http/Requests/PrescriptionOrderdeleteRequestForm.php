<?php

namespace Modules\Sale\Http\Requests;


use App\Http\Requests\FormRequest;

class PrescriptionOrderdeleteRequestForm extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {
        $this->rules['id']      = ['required'];

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
