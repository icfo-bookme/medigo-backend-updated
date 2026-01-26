<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class DeductionFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['head']           = ['required'];
        $rules['department']           = ['required', 'numeric', 'gt:0'];

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
