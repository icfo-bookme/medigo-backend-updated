<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GenericDetails extends BaseModel
{
    protected $table = 'generic_details';
    protected $fillable = ['generic_id','title','description'];
}
