<?php

namespace Modules\Report\Http\Requests;

use App\Http\Requests\FormRequest;

class ClosingHeadFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['label_name']         = ['required','unique:closing_heads,label_name'];
        if(request()->has('update_id'))
        {
            $this->rules['label_name'][1] = 'unique:closing_heads,label_name,'.request()->purchase_id;
        }
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
