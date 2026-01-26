<?php

namespace Modules\Exchange\Entities;

use App\Models\BaseModel;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\Customer;

class Exchange extends BaseModel
{
    protected $fillable = ['warehouse_id', 'return_no', 'invoice_no', 'customer_id', 'customer_name', 'total_price', 'prv_pay_amount', 'paid_amount', 'grand_total', 'reason', 'sale_date', 'exchange_date', 'status', 'return_rcv_status', 'payment_status', 'exchange_qty', 'total_received_qty', 'created_by', 'modified_by'];


    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id','id');
    }
    public function exchange_products()
    {
        return $this->hasMany(ExchangeProduct::class,'exchange_id','id')->with('product');
    }
    public function payments()
    {
        return $this->hasMany(ExchangeSalePayment::class, 'exchange_id', 'id');
    }

    public function user_activity()
    {
        return $this->morphMany(UserActivity::class, 'logable');
    }

    protected $_return_no, $_invoice_no, $_sort_table, $_total_count_filtered;

    public function setReturnNo($return_no)
    {
        $this->_return_no = $return_no;
    }
    public function setInvoiceNo($invoice_no)
    {
        $this->_invoice_no = $invoice_no;
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
        $query = self::with('customer');

        if (!empty(Auth::user()->warehouse_id)) {
            $query->where('warehouse_id',Auth::user()->warehouse_id);
        }
        if (!empty($this->_return_no)) {
            $query->where('return_no', 'like', '%' . $this->_return_no . '%');
        }
        if (!empty($this->_invoice_no)) {
            $query->where('invoice_no', 'like', '%' . $this->_invoice_no . '%');
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

    public function count_filtered(){
        return $this->_total_count_filtered;
    }

    public function count_all(){
        return self::toBase()->get()->count();
    }
}
