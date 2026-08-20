<?php

namespace Modules\Product\Entities;

use App\Models\Tax;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Category;
use App\Models\BaseModel;
use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Product extends BaseModel
{
    protected $fillable = [
        'name', 'slug', 'code', 'brand_id', 'category_id', 'generic_id', 'barcode_symbology', 'image', 'status', 'brief_description', 'medical_overview',
        'quick_tips', 'disclaimer', 'indication', 'product_type', 'created_by', 'modified_by', 'yt_video'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class)->withDefault(['name' => '']);
    }

    public function company()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class, 'product_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function generic()
    {
        return $this->belongsTo(Generic::class, 'generic_id', 'id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class)->withDefault(['name' => 'No Tax', 'rate' => 0]);
    }

    //relations
    public function similar_products(): BelongsToMany
    {
        return $this->belongsToMany(SimilarProduct::class, 'similar_products', 'product_id', 'product_id');
    }

    public function similar_product_list()
    {
        return $this->hasMany(SimilarProduct::class, 'product_id', 'id')->select('id', 'product_id', 'similar_product_id')->with('product');
    }

    public function user_activity()
    {
        return $this->morphMany(UserActivity::class, 'logable');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $_name, $_category_id, $generic_name, $_brand_id, $_status, $_total_count_filtered, $sort_table;

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setGenericName($generic_name)
    {
        $this->_generic_name = $generic_name;
    }

    public function setBrandID($brand_id)
    {
        $this->_brand_id = $brand_id;
    }

    public function setCategoryID($category_id)
    {
        $this->_category_id = $category_id;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    public function setQueryCount($total_count)
    {
        $this->_total_count_filtered = $total_count;
    }

    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }

    private function get_datatable_query()
    {
        $query = self::with('category:id,name', 'generic', 'brand:id,name', 'unit:id,unit_name', 'similar_product_list.product.generic', 'similar_product_list.product.category', 'user_activity', 'user_activity.user:id,name,username,phone');

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
            // Adding a relevance score for name field
            $name = $this->_name;
            $query->selectRaw("*, (CASE WHEN name LIKE ? THEN 1 ELSE 0 END + CASE WHEN name LIKE ? THEN 2 ELSE 0 END + CASE WHEN name LIKE ? THEN 3 ELSE 0 END) as relevance", ["%{$name}%", "{$name}%", "%{$name}"]);
        }
        if (!empty($this->_generic_name)) {
            $generic_name = $this->_generic_name;
            $query->orWhereHas('generic', function ($query) use ($generic_name) {
                $query->where('generic_name', 'like', '%' . $generic_name . '%');
            });
        }
        if (!empty($this->_brand_id)) {
            $query->where('brand_id', $this->_brand_id);
        }
        if (!empty($this->_category_id)) {
            $query->where('category_id', $this->_category_id);
        }
        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
        }
        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
            } else if ($this->_sort_table == 'image_null') {
                $query->orderBy('id', 'asc');
                $query->whereNull('image');
            }
        }

        // query filter count set
        $this->setQueryCount($query->count());

        //order by relevance score first, then by other order parameters
        if (!empty($this->_name)) {
            $query->orderByRaw('relevance DESC');
        }

        //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table header and dirValue is asc or desc
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue); //fetch data order by matching column
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
        }
        return $query;
    }

    public function getDatatableList()
    {
        $query = $this->get_datatable_query();
        if ($this->lengthVlaue != -1) {
            $query->offset($this->startVlaue)->limit($this->lengthVlaue);
        }
        return $query->get();
    }

    public function count_filtered()
    {
        return $this->_total_count_filtered;
    }

    public function count_all()
    {
        return self::count();
    }

    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/
    /*************************************
     * * *  Begin :: Cache Data * * *
     **************************************/
    protected const PRODUCT_CACHE_KEY = 'product_cache_key';

    public static function allProducts()
    {
        return Cache::rememberForever(self::PRODUCT_CACHE_KEY, function () {
            return self::with('category:id,name', 'generic:id,generic_name', 'brand:id,name', 'unit:id,unit_name')->get();
        });
    }

    public static function getProductsWithFilters($search_text = null, $category_id = null, $campaign_id = null)
    {
        // Generate a unique cache key based on the filters
        $cacheKey = self::PRODUCT_CACHE_KEY . md5($search_text . $category_id . $campaign_id);

        return Cache::rememberForever($cacheKey, function () use ($search_text, $category_id, $campaign_id) {
            $query = self::select('id', 'name', 'slug', 'code', 'brand_id', 'category_id', 'generic_id', 'image', 'status', 'product_type')
                ->with('category:id,name', 'generic:id,generic_name', 'brand:id,name', 'unit:id,unit_name', 'productUnits:id,product_id,campaign_id,product_unit_id,item_code,price,campaign_price,discount,qty,alert_qty');

            // Apply relevance scoring and ordering if $search_text is provided
            if (strlen($search_text) > 0) {
                $query->selectRaw(
                    "(CASE
                    WHEN name = ? THEN 10
                    WHEN slug = ? THEN 9
                    WHEN name LIKE ? THEN 8
                    WHEN slug LIKE ? THEN 7
                    WHEN name LIKE ? THEN 6
                    WHEN slug LIKE ? THEN 5
                    WHEN EXISTS(SELECT 1 FROM generics WHERE generics.id = products.generic_id AND generic_name = ?) THEN 4
                    WHEN EXISTS(SELECT 1 FROM generics WHERE generics.id = products.generic_id AND generic_name LIKE ?) THEN 3
                    WHEN EXISTS(SELECT 1 FROM generics WHERE generics.id = products.generic_id AND generic_name LIKE ?) THEN 2
                    ELSE 0
                END) AS relevance_score",
                    [
                        $search_text,
                        $search_text,
                        "$search_text%",
                        "$search_text%",
                        "%$search_text%",
                        "%$search_text%",
                        $search_text,
                        "$search_text%",
                        "%$search_text%",
                    ]
                );

                $query->where(function ($q) use ($search_text) {
                    $q->where('name', 'like', '%' . $search_text . '%')
                        ->orWhere('slug', 'like', '%' . $search_text . '%')
                        ->orWhereHas('productUnits', function ($query) use ($search_text) {
                            $query->where('item_code', 'like', '%' . $search_text . '%');
                        });
                });

                $query->orderByDesc('relevance_score');
            }

            if ($category_id) {
                $query->where('category_id', $category_id);
            }
            if ($campaign_id) {
                $query->whereHas('productUnits', function ($query) use ($campaign_id) {
                    $query->where('campaign_id', $campaign_id);
                });
            }

            if (strlen($search_text) > 0) {
                return $query->orderBy('name', 'asc')->limit(20)->get();
            }

            return $query->orderBy('id', 'DESC')->limit(20)->get(); 
        });
    }

    public static function getSimilarProductsByGenericId($genericId)
    {
        return Cache::rememberForever(self::PRODUCT_CACHE_KEY . $genericId, function () use ($genericId) {
            return self::where('generic_id', $genericId)
                ->with([
                    'generic:id,generic_name', 'company:id,name', 'productUnits:id,product_id,product_unit_id,price,discount,qty',
                    'productUnits.unit:id,unit_name',
                ])
                ->paginate(10);
        });
    }

    public static function flushCache($genericId = null, $search_text = null, $category_id = null, $campaign_id = null)
    {
        Cache::forget(self::PRODUCT_CACHE_KEY);
        // self::allProducts();
        if ($genericId) {
            $genericCacheKey = self::PRODUCT_CACHE_KEY . $genericId;
            Cache::forget($genericCacheKey);
            self::getSimilarProductsByGenericId($genericId);
        }
        if ($search_text || $category_id || $campaign_id) {
            $cacheKey = self::PRODUCT_CACHE_KEY . md5($search_text . $category_id . $campaign_id);
            Cache::forget($cacheKey);
            self::getProductsWithFilters($search_text, $category_id, $campaign_id);
        }
    }

    public static function boot()
    {
        parent::boot();

        static::updated(function ($product) {
            self::flushCache($product->generic_id);
        });

        static::created(function ($product) {
            self::flushCache($product->generic_id);
        });

        static::deleted(function ($product) {
            self::flushCache($product->generic_id);
        });
    }
    /*************************************
     * * *  End :: Cache Data * * *
     **************************************/

       // active scope 
     public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeApiQuickSelect($query){
          return $query->select('id', 'slug', 'category_id', 'name', 'generic_id', 'brand_id', 'image', 'product_type','yt_video')
          ->with([
            'generic:id,generic_name',
            'company:id,name',
            'productUnits:id,product_id,product_unit_id,price,discount,qty,campaign_id,campaign_price',
            'productUnits.unit:id,unit_name',
        ])->active();
    }

    public function scopeApiQuickRelation($query){
        return $query;
    }
    
}
