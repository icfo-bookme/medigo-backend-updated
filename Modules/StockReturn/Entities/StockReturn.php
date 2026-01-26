<?php

namespace Modules\StockReturn\Entities;

use App\Models\BaseModel;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\Customer;
use Modules\Setting\Entities\Warehouse;
use Modules\Supplier\Entities\Supplier;

class StockReturn extends BaseModel
{
    protected $fillable = ['warehouse_id', 'return_no', 'invoice_no', 'customer_id', 'supplier_id', 'customer_name', 'total_price', 'total_deduction', 'tax_rate', 'total_tax', 'grand_total', 'reason', 'date', 'return_status', 'return_date', 'type', 'created_by', 'modified_by', 'payment_method' ,'account_id' ,'reference_no', 'is_paid', 'sale_payment_status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id','id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id','id');
    }

    public function return_products()
    {
        return $this->hasMany(StockReturnProduct::class,'stock_return_id','id')->with('product');
    }

    public function user_activity()
    {
        return $this->morphMany(UserActivity::class, 'logable');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $_type, $_return_no, $_invoice_no, $_start_date, $_end_date, $_customer_id, $_sort_table, $_total_count_filtered;

    //methods to set custom search property value
    public function setType($type)
    {
        $this->_type = $type;
    }
    public function setReturnNo($return_no)
    {
        $this->_return_no = $return_no;
    }
    public function setInvoiceNo($invoice_no)
    {
        $this->_invoice_no = $invoice_no;
    }
    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }
    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }
    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }
    public function setQueryCount($total_count)
    {
        $this->_total_count_filtered = $total_count;
    }

    private function get_datatable_query()
    {
        $query = self::with('customer','supplier')->where('type',$this->_type);
        if (!empty(Auth::user()->warehouse_id)) {
            $query->where('warehouse_id',Auth::user()->warehouse_id);
        }

        //search query
        if (!empty($this->_return_no)) {
            $query->where('return_no', 'like', '%' . $this->_return_no . '%');
        }
        if (!empty($this->_invoice_no)) {
            $query->where('invoice_no', 'like', '%' . $this->_invoice_no . '%');
        }
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereBetween('return_date', [$this->_start_date, $this->_end_date]);
        }
        if (!empty($this->_customer_id)) {
            $query->where('customer_id', $this->_customer_id);
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
        return $this->_total_count_filtered;
    }

    public function count_all()
    {
        return self::toBase()->where('type',$this->_type)->get()->count();
    }
}
