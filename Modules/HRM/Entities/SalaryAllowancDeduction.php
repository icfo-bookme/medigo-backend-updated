<?php

namespace Modules\HRM\Entities;

use Illuminate\Database\Eloquent\Model;

class SalaryAllowancDeduction extends Model
{
    protected $table = 'salary_allowanc_deductions';

    protected $fillable = ['salary_id', 'head_id', 'head_value', 'type'];

    public function allowance()
    {
        return $this->belongsTo(Allowance::class, 'head_id', 'id')->where('type' , 1);
    }

    public function deduction()
    {
        return $this->belongsTo(Deduction::class, 'head_id', 'id')->where('type' , 2);
    }
}
