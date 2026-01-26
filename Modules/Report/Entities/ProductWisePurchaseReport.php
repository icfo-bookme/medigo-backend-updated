<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class ProductWisePurchaseReport extends BaseModel
{
    protected $table = 'purchase_products';

    protected $order = ['p.purchase_date' => 'desc'];

    //custom search column property
    protected $_product_id, $_warehouse_id, $_start_date, $_end_date;

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
        $query = DB::table('purchase_products')
            ->join('purchases', 'purchase_products.purchase_id', 'purchases.id')
            ->join('products as p', 'purchase_products.product_id', 'p.id')
            ->join('units as u', 'purchase_products.sale_unit_id', 'u.id')
            ->selectRaw('purchase_products.*,purchases.invoice_no,purchases.purchase_date,p.name,p.code,u.unit_name,u.unit_code');

        //search query
        if (!empty($this->_product_id)) {
            $query->where('purchase_products.product_id', $this->_product_id);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('purchases.warehouse_id', $this->_warehouse_id);
        }
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereBetween('purchases.purchase_date', [$this->_start_date, $this->_end_date]);
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
        return DB::table('purchase_products')->count();
    }
}
