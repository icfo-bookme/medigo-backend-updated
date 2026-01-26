<?php

namespace Modules\Exchange\Entities;

use App\Models\BaseModel;
use Modules\Product\Entities\Product;

class ExchangeProductReceive extends BaseModel
{
    protected $fillable = ['invoice_no', 'exchange_id', 'product_id', 'product_code', 'price', 'receive_qty', 'sub_total', 'receive_date'];

    public function exchange(){
        return $this->belongsTo(Exchange::class,'exchange_id','id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
