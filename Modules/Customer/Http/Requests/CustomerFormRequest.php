<?php

namespace Modules\Customer\Http\Requests;

use App\Http\Requests\FormRequest;

class CustomerFormRequest extends FormRequest
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
        $this->rules['name'] = ['required', 'string', 'max:100'];
        $this->rules['address'] = ['nullable', 'string'];
        if (request()->update_id) {
            $this->rules['phone'][3] = 'unique:customers,phone,' . request()->update_id;
        } else {
            $this->rules['phone'] = ['required', 'string', 'max:15', 'unique:customers,phone'];
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
