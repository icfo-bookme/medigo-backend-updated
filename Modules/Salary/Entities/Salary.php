<?php

namespace Modules\Salary\Entities;

use App\Models\BaseModel;

class Salary extends BaseModel {
    protected $fillable = ['month','date','salary','monthly_target_achieve','product_target_achieve','ta_td','deduction','claim','salarytable_type','salarytable_id','status','type'];
    protected $table    = 'salaries';
    public function salarytable(){
        return $this->morphTo();
    }
}
