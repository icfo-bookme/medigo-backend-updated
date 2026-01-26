<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkingDay extends BaseModel
{
    protected $fillable = ['updated_by','day','working_status'];

}
