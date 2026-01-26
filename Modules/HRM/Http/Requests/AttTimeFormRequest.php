<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class AttTimeFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['shift']      = ['required'];
        $rules['start_time'] = ['required'];
        $rules['end_time']   = ['required'];
        $rules['status']     = ['required'];
        if(request()->update_id)
        {
            $rules['shift']     = 'required';
            $rules['start_time']= 'required';
            $rules['end_time']  = 'required';
            $rules['status']    = 'required';
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
