<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class LeaveCatFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['leave_category']      = ['required','string','unique:leave_categories,leave_category'];
        $rules['leave_category_description']      = ['required'];
        if(request()->update_id)
        {
            $rules['leave_category']   = 'unique:leave_categories,leave_category,'.request()->update_id;
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
