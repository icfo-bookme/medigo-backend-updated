<?php

namespace Modules\Setting\Http\Requests;

use App\Http\Requests\FormRequest;

class WarehouseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']      = ['required','string','unique:warehouses,name'];
        $rules['phone']     = ['nullable','string'];
        $rules['address']   = ['nullable','string'];
        $rules['deletable'] = ['required','integer'];

        if(request()->update_id)
        {
            $rules['name'] = 'unique:warehouses,name,'.request()->update_id;
        }
        return $rules;
    }

}
