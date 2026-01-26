<?php

namespace Modules\HRM\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabourAttendance extends Model
{
    use HasFactory;

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\HRM\Database\factories\LabourAttendanceFactory::new();
    }
}
