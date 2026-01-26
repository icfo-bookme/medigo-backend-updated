<?php

namespace Modules\Purchase\Http\Requests;

use App\Http\Requests\FormRequest;

class DraftFormRequest extends FormRequest
{
    protected $rules = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $this->rules['draft_no'] = ['required', 'string'];
        $this->rules['amount'] = ['required','numeric'];
        $this->rules['description'] = ['nullable'];
        return $this->rules;

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
