<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;

class BrandRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules['name'] = ['required','string','unique:brands,name'];
        if(request()->update_id)
        {
            $rules['name'][2] = 'unique:brands,name,'.request()->update_id;
        }
        return $rules;
    }
}
