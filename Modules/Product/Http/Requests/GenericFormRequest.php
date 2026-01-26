<?php

namespace Modules\Product\Http\Requests;

use App\Http\Requests\FormRequest;

class GenericFormRequest extends FormRequest
{
    protected $rules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['generic_name']        = ['required'];
        return $this->rules;
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
