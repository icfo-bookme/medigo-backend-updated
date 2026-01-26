<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class LabourLeaveFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['leave_category']    = ['required'];
        $rules['labour_id']       = ['required'];
        $rules['start_date']        = ['required'];
        $rules['end_date']          = ['required'];
        $rules['purpose']           = ['required'];
        $rules['status']            = ['required'];
        if(request()->update_id)
        {
            $rules['leave_category']    = ['required'];
            $rules['labour_id']       = ['required'];
            $rules['start_date']        = ['required'];
            $rules['end_date']          = ['required'];
            $rules['purpose']           = ['required'];
            $rules['status']            = ['required'];
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
