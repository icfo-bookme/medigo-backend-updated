<?php

namespace Modules\Point\Http\Requests;

use App\Http\Requests\FormRequest;

class MoneyWisePointRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['money']   = ['required'];
        $rules['point']   = ['required'];

        return $rules;
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
