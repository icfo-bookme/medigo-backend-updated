<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class ShiftFromRequest extends FormRequest
{
    public function rules()
    {
        $rules['name'] = ['required'];
        $rules['starting_time'] = ['required'];
        $rules['ending_time'] = ['required'];
        $rules['shift_hours'] = ['required', 'numeric', 'gt:0'];
//        $rules['ending_time'] = ['required', 'after:starting_time'];
        $rules['department'] = ['required', 'numeric', 'gt:0'];

        return $rules;
    }

    public function authorize()
    {
        return true;
    }
}
