<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class HolidayFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];
    public function rules(){
        $this->rules['title']                                     = ['required'];
        $this->rules['type']                                      = ['required','numeric','gt:0'];
        $this->rules['start_date']                                 = ['required','date','date_format:Y-m-d'];
        $this->rules['end_date']                                 = ['required','date','date_format:Y-m-d'];
        $this->rules['description']                            = ['nullable'];
        return $this->rules;
    }
    public function messages(){
        return $this->messages;
    }
    public function authorize(){
        return true;
    }
}
