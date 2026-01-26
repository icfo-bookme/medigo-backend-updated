<?php

namespace Modules\Product\Entities;

use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;

class AdjustmentProduct extends Model
{
    protected $fillable = ['adjustment_id', 'product_id', 'qty','action'];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
