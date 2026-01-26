<?php

namespace Modules\Sale\Entities;



use App\Models\BaseModel;
use App\Models\Unit;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;

class OrderPackageProducts extends BaseModel
{

    protected $fillable = ['order_package_id','sale_product_id',
        'sale_unit_id','net_unit_price'
        ,'discount','discount_rate','product_id','qty','total'];

    protected $table = 'order_package_products';

    public function product() {

        return $this->belongsTo(Product::class,'product_id','id');

    }

    public function unit()
    {
        return $this->belongsTo(Unit::class,'sale_unit_id','id');
    }


    public function product_variant(){
        return $this->belongsTo(ProductUnit::class,'sale_product_id','id');
    }

    public function productUnits(){
        return $this->hasMany(ProductUnit::class,'product_id','product_id');
    }

}
