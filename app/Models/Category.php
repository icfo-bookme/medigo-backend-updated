<?php
namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;

class Category extends BaseModel
{

    protected $fillable = ['id', 'parent_id', 'nav_serial', 'cat_code', 'name', 'slug', 'status', 'image', 'created_by', 'modified_by'];

    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        return $this->image ? ('storage/' . PRODUCT_IMAGE_PATH) : asset('storage/category/default.jpg');
    }

    public function childrenCategory()
    {
        return $this->hasMany(Category::class, 'parent_id')->where('status', 1);
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_id')->where('status', 1);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $_name, $status, $sort_table, $_total_count_filtered;

    //methods to set custom search property value

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setTableOrder($sort_table)
    {
        $this->sort_table = $sort_table;
    }

    public function setQueryCount($total_count)
    {
        $this->_total_count_filtered = $total_count;
    }

    private function get_datatable_query()
    {
        $query = self::with('parentCategory');

        //search query
        if (! empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
            // Adding a relevance score for name field
            $name = $this->_name;
            $query->selectRaw("*, (CASE WHEN name LIKE ? THEN 1 ELSE 0 END + CASE WHEN name LIKE ? THEN 2 ELSE 0 END + CASE WHEN name LIKE ? THEN 3 ELSE 0 END) as relevance", ["%{$name}%", "{$name}%", "%{$name}"]);
        }
        if (! empty($this->status)) {
            $query->where('status', $this->status);
        }
        if (! empty($this->sort_table)) {
            if ($this->sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
            }
        }

        // query filter count set
        $this->setQueryCount($query->count());

        //order by relevance score first, then by other order parameters
        if (! empty($this->_name)) {
            $query->orderByRaw('relevance DESC');
        }

                                                                                  //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) {                 //orderValue is the index number of table header and dirValue is asc or desc
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
    protected const CATEGORY_CACHE_KEY = 'category_cache_key';

    public static function allCategories()
    {
        return self::with([
            'childrenCategory:id,parent_id,nav_serial,cat_code,name,slug,image',
            'parentCategory:id,parent_id,nav_serial,cat_code,name,slug,image',
        ])
            ->select('id', 'parent_id', 'nav_serial', 'cat_code', 'name', 'slug', 'image')
            ->where('status', 1)
            ->orderBy('nav_serial', 'desc')
            ->get();

    }

    public static function getCategoriesWithFilters($search_text = null, $category_id = null)
    {
        // Generate a unique cache key based on the filters
        $cacheKey = self::CATEGORY_CACHE_KEY . md5($search_text . $category_id);

        return Cache::rememberForever($cacheKey, function () use ($search_text, $category_id) {
            $query = self::select('id', 'parent_id', 'nav_serial', 'cat_code', 'name', 'slug', 'image')
                ->with(
                    'parentCategory:id,parent_id,nav_serial,cat_code,name,slug',
                    'products:id,name,slug,code,brand_id,category_id,generic_id,image,status,product_type'
                )
                ->where('status', 1);

            // Add relevance scoring if search_text is provided
            if (strlen($search_text) > 0) {
                $query->selectRaw(
                    "(CASE
                WHEN name = ? THEN 10
                WHEN slug = ? THEN 9
                WHEN name LIKE ? THEN 8
                WHEN slug LIKE ? THEN 7
                WHEN name LIKE ? THEN 6
                WHEN slug LIKE ? THEN 5
                ELSE 0
            END) AS relevance_score",
                    [
                        $search_text,
                        $search_text,
                        "$search_text%",
                        "$search_text%",
                        "%$search_text%",
                        "%$search_text%",
                    ]
                );

                $query->where(function ($q) use ($search_text) {
                    $q->where('name', 'like', '%' . $search_text . '%')
                        ->orWhere('slug', 'like', '%' . $search_text . '%');
                });

                $query->orderByDesc('relevance_score');
            }

            if ($category_id) {
                $query->where('id', $category_id);
            }

            if (strlen($search_text) > 0) {
                return $query->orderBy('name', 'asc')->limit(20)->get();
            }

            return $query->orderBy('id', 'DESC')->limit(20)->get();
        });
    }

    public static function flushCache()
    {
        Cache::forget(self::CATEGORY_CACHE_KEY);
        self::allCategories();

        DB::table('ecom_caches')->insert([
            ['key' => 'category'],
            ['key' => 'product'],
            ['key' => 'brand'],
        ]);

    }

    public static function boot()
    {
        parent::boot();

        static::updated(function () {
            self::flushCache();
        });

        static::created(function () {
            self::flushCache();
        });

        static::deleted(function () {
            self::flushCache();
        });
    }
    /***********************************
     * * *  Begin :: Cache Data * * *
     ************************************/
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    public function scopeApiQuickSelect($query)
    {
        return $query->select('id', 'name', 'slug');
    }

    // has product scope 
    public function scopeCheckHasProduct($query){
        return $query->has('products');
    }

}
