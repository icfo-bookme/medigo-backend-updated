<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;

class SaleProductReturn extends BaseModel
{
    protected $fillable = ['sale_id','invoice_no','return_date','warehouse_id','product_id','price','return_qty','sub_total'];
    protected $table    = 'sale_product_returns';

    public function sale(){
        return $this->belongsTo(Sale::class,'sale_id','id');
    }
    public function warehouse(){
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
    public function product(){
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
