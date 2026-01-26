<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Generic extends BaseModel
{
    protected $fillable = ['generic_name', 'slug', 'status'];


    public function genericDetails()
    {
        return $this->hasMany(GenericDetails::class, 'generic_id', 'id');
    }

    public function similarProducts()
    {
        return $this->hasMany(Product::class, 'generic_id', 'id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $_generic_name, $_sort_table;

    //methods to set custom search property value
    public function setName($generic_name)
    {
        $this->_generic_name = $generic_name;
    }

    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }

    private function get_datatable_query()
    {
        $query = self::toBase();

        //search query
        if (!empty($this->_generic_name)) {
            $query->where('generic_name', 'like', '%' . $this->_generic_name . '%');
            // Adding a relevance score for name field
            $name = $this->_generic_name;
            $query->selectRaw("*, (CASE WHEN generic_name LIKE ? THEN 1 ELSE 0 END + CASE WHEN generic_name LIKE ? THEN 2 ELSE 0 END + CASE WHEN generic_name LIKE ? THEN 3 ELSE 0 END) as relevance", ["%{$name}%", "{$name}%", "%{$name}"]);

            //order by relevance score first, then by other order parameters
            $query->orderByRaw('relevance DESC');
        }
        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
            }
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

    protected static function booted()
    {
        static::created(function ($model) {
            Cache::forget('combined_results');
        });

        static::updated(function ($model) {
            Cache::forget('combined_results');
        });
    }


}
