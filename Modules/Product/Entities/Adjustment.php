<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;
use App\Models\UserActivity;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;

class Adjustment extends BaseModel
{
    protected $fillable = ['warehouse_id','adjustment_no','item','total_qty','note','created_by','modified_by'];

    public function products()
    {
        return $this->belongsToMany(Product::class,'adjustment_products','adjustment_id','product_id','id','id')
        ->withPivot('id', 'qty', 'action')
        ->withTimestamps();
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
    public function user_activity()
    {
        return $this->morphMany(UserActivity::class, 'logable');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_adjustment_no;
    protected $_warehouse_id;
    protected $_from_date;
    protected $_to_date;

    //methods to set custom search property value
    public function setAdjustmentNo($adjustment_no)
    {
        $this->_adjustment_no = $adjustment_no;
    }
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }
    public function setFromDate($from_date)
    {
        $this->_from_date = $from_date;
    }
    public function setToDate($to_date)
    {
        $this->_to_date = $to_date;
    }


    private function get_datatable_query()
    {
        if (permission('adjustment-bulk-delete')){
            if(auth()->user()->warehouse_id)
            {
                $this->column_order = ['id','id','adjustment_no', 'item','total_qty', 'created_at', null];
            }else{
                $this->column_order = ['id','id','adjustment_no','warehouse_id', 'item','total_qty', 'created_at', null];
            }

        }else{
            if(auth()->user()->warehouse_id)
            {
                $this->column_order = ['id','adjustment_no', 'item','total_qty', 'created_at', null];
            }else{
                $this->column_order = ['id','adjustment_no','warehouse_id', 'item','total_qty', 'created_at', null];
            }
        }

        $query = self::with('warehouse');
        if(auth()->user()->warehouse_id)
        {
            $query->where('warehouse_id',  auth()->user()->warehouse_id);
        }
        //search query
        if (!empty($this->_adjustment_no)) {
            $query->where('adjustment_no', 'like', '%' . $this->_adjustment_no . '%');
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id',  $this->_warehouse_id);
        }
        if (!empty($this->_from_date)) {
            $query->whereDate('created_at', '>=',$this->_from_date);
        }
        if (!empty($this->_to_date)) {
            $query->whereDate('created_at', '<=',$this->_to_date);
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
        $query = self::toBase();
        if(auth()->user()->warehouse_id)
        {
            $query->where('warehouse_id',  auth()->user()->warehouse_id);
        }
        return $query->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
