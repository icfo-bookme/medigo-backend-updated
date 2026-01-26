<?php

namespace Modules\Coupon\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\Sale;

class CouponReport extends BaseModel
{
    protected $fillable = ['coupon_type','name','type','value', 'start_date', 'end_date', 'status'];

    protected $table = 'coupons';

    protected $_coupon_id, $_coupon_type, $sort_table;

    public function setCouponId($coupon_id)
    {
        $this->_coupon_id = $coupon_id;
    }
    public function setCouponType($coupon_type)
    {
        $this->_coupon_type = $coupon_type;
    }
    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'coupon_id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    private function get_datatable_query()
    {
        $query = self::with(['sales' => function($query) {
            $query->select('coupon_id',
                DB::raw('GROUP_CONCAT(invoice_no) as invoice_nos'),
                DB::raw('GROUP_CONCAT(id) as sale_ids'))
                ->groupBy('coupon_id');
        }])
            ->withCount('sales')
            ->withSum('sales', 'total_discount')
            ->withSum('sales', 'coupon_discount_value');


        if (!empty($this->_coupon_id)) {
            $query->where('id', $this->_coupon_id);
        }
        if (!empty($this->_coupon_type)) {
            $query->where('coupon_type', $this->_coupon_type);
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
        $query = $this->get_datatable_query();
        return $query->get()->count();
    }

    public function count_all()
    {
        return self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/
}
