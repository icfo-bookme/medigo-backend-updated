<?php

namespace Modules\StockReturn\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;

class StockReturnProduct extends Model
{
    protected $fillable = ['warehouse_id', 'stock_return_id', 'invoice_no', 'product_id', 'product_variant_id', 'item_code', 'return_qty', 'unit_id', 'product_rate', 'deduction_rate', 'deduction_amount', 'total'];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function product_variant(){
        return $this->belongsTo(ProductUnit::class, 'product_variant_id', 'id');
    }
}
