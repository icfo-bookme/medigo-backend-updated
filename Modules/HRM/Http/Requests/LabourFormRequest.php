<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class LabourFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['labour_id']               = ['required','string','unique:labours,name'];
        $rules['name']                      = ['required','string'];
        $rules['contact_no_one']            = ['required'];
        $rules['gender']                    = ['required'];
        $rules['present_address']           = ['required'];
        $rules['joining_date']              = ['required'];
        $rules['branch_id']                 = ['required'];
        $rules['basic_salary']              = ['required'];
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
