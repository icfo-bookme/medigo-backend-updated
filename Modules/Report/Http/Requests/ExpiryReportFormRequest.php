<?php

namespace Modules\Report\Http\Requests;

use App\Http\Requests\FormRequest;

class ExpiryReportFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];
    
    public function rules()
    {
        $this->rules['start_date']         = ['required', 'date'];
        $this->rules['end_date']   = ['required', 'date', 'after:start_date'];
        
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
