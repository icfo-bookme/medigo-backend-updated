<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\SaleProduct;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\AdjustmentProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends BaseModel
{
    protected $table = 'product_variants';
    protected $fillable = ['product_id', 'item_name', 'item_code', 'item_price', 'item_qty'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function scopeFindExactProductWithCode($query, $product_id, $item_code)
    {
    	return $query->where([
            ['product_id', $product_id],
            ['item_code', $item_code],
        ]);
    }

    public function sales()
    {
        return $this->hasMany(SaleProduct::class,'product_variant_id','id');
    }
    // public function purchases()
    // {
    //     return $this->hasMany(PurchaseProduct::class,'product_variant_id','id');
    // }
    public function adjustments()
    {
        return $this->hasMany(AdjustmentProduct::class,'product_variant_id','id');
    }
    // public function quotations()
    // {
    //     return $this->hasMany(QuotationProduct::class,'product_variant_id','id');
    // }
}
