<?php

namespace Modules\Stock\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;
use Modules\Setting\Entities\Warehouse;

class WarehouseProduct extends BaseModel {
    protected $fillable = ['product_id','product_unit_id', 'item_code','price','discount','qty'];
    protected $table    = 'product_units';
    protected $product_id, $company_id, $_sort_table, $_total_count_filtered;

    public function product(){
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function setProductID($product_id){
        $this->product_id = $product_id;
    }
    public function setCompanyId($company_id){
        $this->company_id = $company_id;
    }
    public function setTableOrder($sort_table){
        $this->_sort_table = $sort_table;
    }
    public function setQueryCount($total_count)
    {
        $this->_total_count_filtered = $total_count;
    }

    private function get_datatable_query(){
        $this->column_order = ['product_id','product_unit_id', 'item_code','price','discount','qty',null];
        $query  =  ProductUnit::with('unit','product','product.company');

        if (!empty($this->product_id)) {
            $query->where('product_id', $this->product_id);
        }
        if (!empty($this->company_id)) {
            $company_id = $this->company_id;
            $query->whereHas('product', function ($que) use ($company_id) {
                $que->where('brand_id', $company_id);
            });
        }
        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
            }
        }

        // query filter count set
        $this->setQueryCount($query->count());

        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table header and dirValue is asc or desc
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue); //fetch data order by matching column
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
        }
        return $query;
    }
    public function getDatatableList(){
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
        return self::toBase()->get()->count();
    }


//    public function count_filtered(){
//        $query = $this->get_datatable_query();
//        return $query->get()->count();
//    }
//    public function count_all(){
//        return DB::table('warehouse_products as wp')
//               ->join('warehouses as w','wp.warehouse_id','=','w.id')
//               ->join('products as p','wp.product_id','=','p.id')
//               ->join('categories as c','p.category_id','=','c.id')
//               ->join('units as u','p.unit_id','=','u.id')
//               ->where([['wp.qty','!=',0]])
//               ->select('w.name as warehouseName','p.name as productName','p.code as productCode','c.name as categoryName','u.unit_code as unitCode','u.unit_name as unitName','p.price as productPrice','wp.qty as qty')
//               ->get()->count();
//    }
}
