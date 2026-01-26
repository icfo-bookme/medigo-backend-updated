<?php
namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\Customer;
use Modules\Setting\Entities\Warehouse;



class TodaySalesReport extends BaseModel
{
    protected $table = 'sales';
    protected $fillable = [  'invoice_no', 'warehouse_id', 'customer_id', 'item', 'total_qty', 'total_discount',
    'total_tax', 'total_price', 'order_tax_rate', 'order_tax', 'order_discount_per', 'order_discount', 'shipping_cost',
     'grand_total', 'adjustment_per', 'adjustment', 'net_total', 'paid_amount', 'change_amount', 'payment_method',
     'account_id', 'reference_no', 'sale_date', 'delivery_status', 'delivery_date', 'created_by', 'modified_by'
   ];


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id','id');
    }


     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_invoice_no;
    protected $_warehouse_id;

    //methods to set custom search property value
    public function setInvoiceNo($invoice_no)
    {
        $this->_invoice_no = $invoice_no;
    }

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    private function get_datatable_query()
    {
        if(auth()->user()->warehouse_id)
        {
            $this->column_order = ['id','invoice_no', 'item','total_qty','total_price','order_tax','order_discount','shipping_cost','grand_total', 'adjustment','net_total','paid_amount', 'change_amount'];
        }else{
            $this->column_order = ['id','invoice_no', 'warehouse_id','item','total_qty','total_price','order_tax','order_discount','shipping_cost','grand_total', 'adjustment','net_total','paid_amount', 'change_amount'];
        }

        $query = self::with('warehouse','customer')->where('sale_date',date('Y-m-d'));
        if(auth()->user()->warehouse_id)
        {
            $query->where('warehouse_id',  auth()->user()->warehouse_id);
        }
        //search query
        if (!empty($this->_invoice_no)) {
            $query->where('invoice_no', 'like', '%' . $this->_invoice_no . '%');
        }

        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id);
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
        return $this->get_datatable_query()->count();
    }

    public function count_all()
    {
        $query = self::with('warehouse','customer')->where('sale_date',date('Y-m-d'));
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
