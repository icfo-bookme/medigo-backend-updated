<?php

namespace Modules\Report\Http\Requests;

use App\Http\Requests\FormRequest;

class ClosingFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;

    public function rules()
    {
        $this->rules['date'] = ['required', 'unique:daily_closings,date', 'date_format:Y-m-d'];
        $this->rules['title'] = ['required'];
        $this->rules['closing_amount'] = ['nullable', 'numeric', 'gte:0'];
        $this->rules['note'] = ['nullable'];
//        if (request()->has('purchase_id')) {
//            $this->rules['date'][1] = 'unique:daily_closings,date,' . request()->purchase_id;
//        }

        if (request()->has('closing')) {
            foreach (request()->closing as $key => $value) {
                $this->rules   ['closing.' . $key . '.amount'] = ['required', 'numeric', 'gte:0'];
                $this->rules   ['closing.' . $key . '.closing_head_id'] = ['required', 'numeric', 'gte:0'];
                $this->messages['closing.' . $key . '.amount.required'] = 'This field is required';
                $this->messages['closing.' . $key . '.amount.numeric'] = 'The value must be numeric';
                $this->messages['closing.' . $key . '.amount.gte'] = 'The value must be greater than or equal 0';
            }
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
