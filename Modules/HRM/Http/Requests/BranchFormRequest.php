<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class BranchFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']      = ['required'];
        if(request()->update_id)
        {
            $rules['name']   = 'required';
        }
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
