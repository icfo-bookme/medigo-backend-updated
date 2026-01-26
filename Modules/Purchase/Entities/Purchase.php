<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use App\Models\UserActivity;
use Modules\Product\Entities\Product;
use Modules\Supplier\Entities\Supplier;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\Purchase\Entities\PurchaseProduct;

class Purchase extends BaseModel
{
    protected $fillable = [
        'invoice_no', 'supplier_id', 'item', 'total_qty', 'total_discount', 'total_tax', 'total_cost', 'order_tax_rate',
        'order_tax', 'order_discount', 'shipping_cost', 'grand_total', 'paid_amount', 'due_amount', 'purchase_status',
        'payment_status', 'payment_method', 'document', 'note', 'purchase_date', 'created_by', 'modified_by'    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function  purchase_products()
    {
        return $this->hasMany(PurchaseProduct::class,'purchase_id','id');
    }

    public function purchase_payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function user_activity()
    {
        return $this->morphMany(UserActivity::class, 'logable');
    }

     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_invoice_no, $_start_date, $_end_date ,$_supplier_id, $_purchase_status, $_payment_status, $sort_table;

    //methods to set custom search property value
    public function setInvoiceNo($invoice_no)
    {
        $this->_invoice_no = $invoice_no;
    }
    public function setFromDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setToDate($end_date)
    {
        $this->_end_date = $end_date;
    }
    public function setSupplierID($supplier_id)
    {
        $this->_supplier_id = $supplier_id;
    }
    public function setPurchaseStatus($purchase_status)
    {
        $this->_purchase_status = $purchase_status;
    }
    public function setPaymentStatus($payment_status)
    {
        $this->_payment_status = $payment_status;
    }
    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }


    private function get_datatable_query()
    {
        $query = self::with('supplier:id,name,company_name,mobile', 'user_activity', 'user_activity.user:id,name,username,phone');

        //search query
        if (!empty($this->_invoice_no)) {
            $query->where('invoice_no', 'like', '%' . $this->_invoice_no . '%');
        }
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereBetween('purchase_date', [$this->_start_date, $this->_end_date]);
        }
        if (!empty($this->_supplier_id)) {
            $query->where('supplier_id', $this->_supplier_id);
        }
        if (!empty($this->_purchase_status)) {
            $query->where('purchase_status', $this->_purchase_status);
        }
        if (!empty($this->_payment_status)) {
            $query->where('payment_status', $this->_payment_status);
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
