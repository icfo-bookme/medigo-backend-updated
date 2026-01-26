<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;

class ProductAttributeOption extends BaseModel
{
    protected $fillable = ['product_id', 'product_attribute_id', 'name'];
}
