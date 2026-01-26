<?php

namespace Modules\Approval\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Modules\Setting\Entities\Warehouse;
use Modules\Stock\Entities\StockTransferWarehouseProduct;

class StockTransferApprove extends BaseModel {
    protected $fillable = ['transfer_date','invoice_no','transfer_warehouse_id','receive_warehouse_id','total_qty','status','created_id','approved_id'];
    protected $table    = 'stock_transfers';
    public function createdBy(){
        return $this->belongsTo(User::class,'created_id','id');
    }
    public function approvedBy(){
        return $this->belongsTo(User::class,'approved_id','id');
    }
    public function transferWarehouse(){
        return $this->belongsTo(Warehouse::class,'transfer_warehouse_id','id');
    }
    public function receiveWarehouse(){
        return $this->belongsTo(Warehouse::class,'receive_warehouse_id','id');
    }
    public function stockTransferWarehouseProductList(){
        return $this->hasMany(StockTransferWarehouseProduct::class,'stock_transfer_id','id');
    }
    private function get_datatable_query(){
        $this->column_order = ['transfer_date','invoice_no','transfer_warehouse_id','receive_warehouse_id','total_qty','status','created_by', null];
        $query              = self::with('transferWarehouse','receiveWarehouse','createdBy','approvedBy');
        if (isset($this->orderValue) && isset($this->dirValue)) {
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue);
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
    public function count_filtered(){
        $query = $this->get_datatable_query();
        return $query->get()->count();
    }
    public function count_all(){
        return self::toBase()->get()->count();
    }
}
