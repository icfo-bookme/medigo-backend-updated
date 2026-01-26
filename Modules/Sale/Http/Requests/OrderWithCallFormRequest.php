<?php

namespace Modules\Sale\Http\Requests;

use App\Http\Requests\FormRequest;

class OrderWithCallFormRequest extends FormRequest {
    protected $rules = [];
    protected $messages = [];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {


        $this->rules['facebook'] = ['required'];
        $this->rules['whatsapp'] = ['required'];
        $this->rules['mobile'] = ['required'];

        return $this->rules;
    }

    public function messages() {
        return $this->messages;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }
}
