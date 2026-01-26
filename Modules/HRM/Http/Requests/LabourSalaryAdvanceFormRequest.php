<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class LabourSalaryAdvanceFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['labour_id']             = ['required'];
        $rules['advance_month']         = ['required'];
        $rules['advance_amount']        = ['required'];
        $rules['advance_description']   = ['required'];
        $rules['status']                = ['required'];
        if(request()->update_id)
        {
            $rules['labour_id']             = ['required'];
            $rules['advance_month']         = ['required'];
            $rules['advance_amount']        = ['required'];
            $rules['advance_description']   = ['required'];
            $rules['status']                = ['required'];
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
