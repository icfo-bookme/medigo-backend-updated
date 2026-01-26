<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest;


class CategoryFormRequest extends FormRequest
{
    protected $rules = [];

    public function authorize(){
        return true;
    }

    public function rules(){
        $rules['name']          = ['required','string','unique:categories,name'];
        $rules['cat_code']      = ['required'];
//        $rules['image']         = ['required'];
//        $this->rules['image']   = ['nullable','image','mimes:png,jpg,jpeg,svg,webp','max:2048'];
        if(request()->update_id)
        {
            $rules['name'][2] = 'unique:categories,name,'.request()->update_id;
        }
        return $rules;
    }
}
