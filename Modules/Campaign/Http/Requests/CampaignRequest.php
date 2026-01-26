<?php

namespace Modules\Campaign\Http\Requests;

use App\Http\Requests\FormRequest;

class CampaignRequest extends FormRequest
{
 
    
    protected $rules = [];

    public function rules()
    {
    
           $rules['campaign_type'] = 'required';
           $rules['name'] = 'required';
           $rules['slug'] = 'required';
           $rules['discount_type'] = 'required';
           $rules['discount_amount'] = 'required';
           $rules['image'] = 'nullable';
           $rules['start_date'] = 'required';
           $rules['end_date'] = 'required';
 
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
