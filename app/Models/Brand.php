<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Brand extends BaseModel
{
    protected $fillable = ['name', 'status', 'created_by', 'modified_by'];


    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $_name, $status, $_sort_table;

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
        $this->_sort_table = $sort_table;
    }


    private function get_datatable_query()
    {
        $query = self::toBase();

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
            // Adding a relevance score for name field
            $name = $this->_name;
            $query->selectRaw("*, (CASE WHEN name LIKE ? THEN 1 ELSE 0 END + CASE WHEN name LIKE ? THEN 2 ELSE 0 END + CASE WHEN name LIKE ? THEN 3 ELSE 0 END) as relevance", ["%{$name}%", "{$name}%", "%{$name}"]);
        }
        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }
        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
            }
        }

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
        return $this->get_datatable_query()->count();
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

    protected const PRODUCT_BRAND = '_brands';

    public static function allBrands()
    {
        return Cache::rememberForever(self::PRODUCT_BRAND, function () {
            return self::where('status', 1)->orderBy('name', 'asc')->get();
        });
    }

    public static function flushCache()
    {
        Cache::forget(self::PRODUCT_BRAND);
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
     * * *  End :: Cache Data * * *
     ************************************/
}
