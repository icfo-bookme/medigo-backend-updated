<?php

namespace Modules\Exchange\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;

class ExchangeProduct extends Model
{
    protected $fillable = ['exchange_id', 'invoice_no', 'old_product_id', 'old_product_code', 'old_stock_qty', 'received_qty', 'old_price',
        'product_id', 'product_code', 'stock_qty', 'price', 'old_exchange_qty', 'charge_amount'];

    public function product()
    {
        return $this->belongsTo(Product::class,'old_product_id','id');
    }

    public function product_variant()
    {
        return $this->belongsTo(ProductUnit::class,'old_product_code','item_code');
    }

    public function exc_product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function exc_product_variant()
    {
        return $this->belongsTo(ProductUnit::class,'product_code','product_code');
    }
}
