<?php

namespace Modules\HRM\Entities;

use Illuminate\Database\Eloquent\Model;

class AssignLeave extends Model
{

    protected $table = 'assign_leaves';
    protected $fillable = ['employee_id', 'leave_category_id', 'number_of_days', 'created_by', 'modified_by'];

}
