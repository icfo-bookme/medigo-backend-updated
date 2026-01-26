<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;

class ProductAttribute extends BaseModel
{
    protected $fillable = ['product_id','attribute_id'];
}
