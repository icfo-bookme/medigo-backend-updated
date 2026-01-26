<?php

namespace Modules\Department\Http\Requests;

use App\Http\Requests\FormRequest;

class DepartmentFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']          = ['required','string','unique:departments,name'];
        $rules['depart_code']   = ['required','string','unique:departments,depart_code'];
        $rules['deletable']     = ['required'];
        if(request()->update_id)
        {
            $rules['name']         = 'unique:departments,name,'.request()->update_id;
            $rules['depart_code']  = 'unique:departments,depart_code,'.request()->update_id;
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
