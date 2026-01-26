<?php

namespace Modules\Coupon\Entities;

use App\Models\BaseModel;
use App\Models\Category;

class CouponCategory extends BaseModel
{
    protected $fillable = ['coupon_id', 'category_id', 'status', 'created_at', 'updated_at'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    private function get_datatable_query()
    {
        $query = self::with('coupon', 'category');

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
        $query = $this->get_datatable_query();
        return $query->get()->count();
    }

    public function count_all()
    {
        return self::toBase()->get()->count();
    }
}
