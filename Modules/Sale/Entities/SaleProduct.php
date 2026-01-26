<?php

namespace Modules\Sale\Entities;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;
use Modules\Setting\Entities\Warehouse;

class SaleProduct extends Model
{
    protected $fillable = [ 'sale_id', 'product_id','product_variant_id', 'serial_no', 'qty','return_qty', 'sale_unit_id', 'net_unit_price', 'discount', 'discount_rate', 'tax_rate', 'tax', 'total', 'return_status', 'order_type'];
    public function sale(){
        return $this->belongsTo(Sale::class,'sale_id','id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','id');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class,'sale_unit_id','id');
    }

    public function product_variant(){
        return $this->belongsTo(ProductUnit::class,'product_variant_id','id');
    }

    public function productUnits(){
        return $this->hasMany(ProductUnit::class,'product_id','product_id');
    }
}
