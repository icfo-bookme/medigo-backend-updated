<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class EmployeeFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['employee_id']               = ['required','string','unique:employees,name'];
        $rules['username']                  = ['required','string','unique:employees,username'];
        $rules['name']                      = ['required','string'];
        $rules['email']                     = ['required','unique:employees,email'];
        $rules['contact_no_one']            = ['required'];
        $rules['gender']                    = ['required'];
        $rules['present_address']           = ['required'];
        $rules['blood_group']               = ['required'];
        $rules['joining_date']              = ['required'];
        $rules['id_number']                 = ['required'];
        $rules['branch_id']                 = ['required'];
        $rules['department_id']             = ['required'];
        $rules['designation_id']            = ['required'];
        $rules['marital_status']            = ['required'];
        $rules['date_of_birth']             = ['required'];
        $rules['academic_qualification']    = ['required'];
        $rules['experience']                = ['required'];
        $rules['previous_workplace']        = ['nullable'];

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
