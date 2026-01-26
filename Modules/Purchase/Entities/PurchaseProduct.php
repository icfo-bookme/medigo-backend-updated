<?php

namespace Modules\Purchase\Entities;

use App\Models\Unit;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\ProductUnit;
use Modules\Purchase\Entities\Purchase;
use Modules\Product\Entities\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseProduct extends Model
{
    protected $fillable = ['purchase_id', 'product_id','product_variant_id', 'serial_no','qty', 'free_qty', 'received', 'purchase_unit_id',
        'net_unit_cost', 'discount', 'tax_rate', 'tax', 'total', 'expiry_date'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class,'purchase_id','id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }
    public function product_variant()
    {
        return $this->belongsTo(ProductUnit::class,'product_id','id');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class,'purchase_unit_id','id');
    }
}
