<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;
use App\Models\Unit;
use Illuminate\Support\Facades\Cache;
use Modules\Campaign\Entities\Campaign;

class ProductUnit extends BaseModel
{
    protected $table = 'product_units';
    protected $fillable = ['product_id', 'campaign_id', 'product_unit_id', 'item_code', 'price', 'campaign_price', 'discount', 'qty', 'alert_qty'];
    // protected $with = ['unit', 'product', 'campaign'];

    public function getCampaignPriceAttribute($value)
    {
        $campaign_price  = $value;

        if (!isset($this->campaign)){
          return $campaign_price = 0.00;
        }

        if (isset($this->campaign)) {
            if(($this->campaign?->status == 0)){
               return $campaign_price = 0.00;
            }

            if(($is_not_start = $this->campaign?->start_date >= now()) ||  ($is_dead = $this->campaign?->end_date <= now())){
              return $campaign_price = 0.00;
            }
        }

        return $campaign_price;
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'product_unit_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id')
           ->select('id', 'name' ,'campaign_type', 'slug', 'start_date', 'end_date', 'status', 'discount_type', 'discount_amount');
    }

    /*************************************
     * * *  Begin :: Cache Data * * *
     **************************************/
    protected const PRODUCT_UNIT_CACHE_KEY = 'product_unit_cache_key';

    public static function getProductUnitsByProductId($product_id)
    {
        return Cache::rememberForever(self::PRODUCT_UNIT_CACHE_KEY . '_' . $product_id, function () use ($product_id) {
            return self::with('product', 'unit')->where('product_id', $product_id)->get();
        });
    }

    public static function flushCache($id = null)
    {
        Cache::forget(self::PRODUCT_UNIT_CACHE_KEY);
        if ($id) {
            Cache::forget(self::PRODUCT_UNIT_CACHE_KEY . '_' . $id);
        }
        self::getProductUnitsByProductId($id);
    }

    public static function boot()
    {
        parent::boot();
        static::created(function () {
            self::flushCache();
        });
        static::updated(function () {
            self::flushCache();
        });
        static::deleted(function () {
            self::flushCache();
        });
    }
}
