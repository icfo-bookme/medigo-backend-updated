<?php

namespace Modules\Product\Entities;



use App\Models\BaseModel;
use App\Models\Tax;

class SimilarProduct extends BaseModel
{

    protected $fillable = ['product_id','similar_product_id'];

    protected  $table = 'similar_products';


    public function product()
    {
        return $this->belongsTo(Product::class,'similar_product_id')
            ->select('id','name','slug','code','category_id','generic_id','brand_id','image','product_type')->with('category:id,name','generic:id,generic_name,slug');
    }
}
