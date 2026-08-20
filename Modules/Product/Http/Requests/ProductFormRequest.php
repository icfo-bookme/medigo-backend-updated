<?php

namespace Modules\Product\Http\Requests;

use App\Http\Requests\FormRequest;

class ProductFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['name']        = ['required','string','unique:products,name'];
//        $this->rules['code'] = ['required','string','unique:products,code'];
        if(request()->update_id){
            $this->rules['name'][2] = 'unique:products,name,'.request()->update_id;
            $this->rules['code'][2] = 'unique:products,code,'.request()->update_id;
        }

        $this->rules['brand_id']    = ['required'];
        $this->rules['product_id']  = ['nullable'];
 
        $this->rules['brief_description'] = ['nullable'];
        $this->rules['medical_overview'] = ['nullable'];
        $this->rules['quick_tips'] = ['nullable'];
        $this->rules['disclaimer'] = ['nullable'];
        $this->rules['indication'] = ['nullable'];
        $this->rules['yt_video'] = ['nullable'];

        $this->rules['generic_id']  = ['required'];
        $this->rules['category_id'] = ['required'];
        $this->rules['image']       = ['nullable','image','mimes:png,jpg,jpeg,svg,webp','max:2048'];
        $this->messages['category_id.required'] = 'The category name field is required';
        $this->messages['unit_id.required']     = 'The unit name field is required';
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
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
