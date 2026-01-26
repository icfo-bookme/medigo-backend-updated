<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;


class ProductWiseSalesReport extends BaseModel
{
    protected $table = 'sale_products';
    protected $guarded = [];

    protected $order = ['s.sale_date' => 'desc'];
    //custom search column property
    protected $_product_id;
    protected $_warehouse_id;
    protected $_start_date;
    protected $_end_date;

    //methods to set custom search property value
    public function setProductID($product_id)
    {
        $this->_product_id = $product_id;
    }

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }

    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    private function get_datatable_query()
    {
        $warehouse_id = $this->_warehouse_id;
        $query = DB::table('sale_products as sp')
            ->join('sales as s', 'sp.sale_id', '=', 's.id')
            ->join('products as p', 'sp.product_id', '=', 'p.id')
            ->join('units as u', 'sp.sale_unit_id', '=', 'u.id')
            ->selectRaw('sp.*,s.memo_no,s.sale_date,p.name,p.code,u.unit_name,u.unit_code');

        //search query
        if (!empty($this->_product_id)) {
            $query->where('sp.product_id', $this->_product_id);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('s.warehouse_id', $this->_warehouse_id);
        }
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereDate('s.sale_date', '>=', $this->_start_date)
                ->whereDate('s.sale_date', '<=', $this->_end_date);
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
        $query = DB::table('sale_products as sp')
            ->join('sales as s', 'sp.sale_id', '=', 's.id')
            ->join('products as p', 'sp.product_id', '=', 'p.id');
        if (!empty($this->_warehouse_id)) {
            $query->where('s.warehouse_id', $this->_warehouse_id);
        }
        return $query->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/
}
