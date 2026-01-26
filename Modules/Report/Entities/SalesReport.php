<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Modules\Product\Entities\Product;
use Modules\Customer\Entities\Customer;
use Modules\Setting\Entities\Warehouse;

class SalesReport extends BaseModel
{
    protected $table = 'sales';
    protected $fillable = ['invoice_no', 'warehouse_id', 'customer_id', 'item', 'total_qty', 'total_discount',
        'total_tax', 'total_price', 'order_tax_rate', 'order_tax', 'order_discount_per', 'order_discount', 'shipping_cost',
        'grand_total', 'adjustment_per', 'adjustment', 'net_total', 'paid_amount', 'change_amount', 'payment_method',
        'account_id', 'reference_no', 'sale_date', 'delivery_status', 'delivery_date', 'created_by', 'modified_by'
    ];


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'sale_products', 'sale_id', 'product_id', 'id', 'id')
            ->withPivot('id', 'serial_no', 'qty', 'sale_unit_id', 'net_unit_price', 'discount', 'discount_rate', 'tax_rate', 'tax', 'total')
            ->withTimestamps();
    }


    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $_invoice_no, $_start_date, $_end_date, $_warehouse_id, $_sort_table, $_order_source, $_total_count_filtered;

    //methods to set custom search property value
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
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }
    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }
    public function setOrderSource($order_source)
    {
        $this->_order_source = $order_source;
    }
    public function setQueryCount($total_count)
    {
        $this->_total_count_filtered = $total_count;
    }


    private function get_datatable_query()
    {
        $query = self::with('warehouse', 'customer');

        switch ($this->_order_source) {
            case 'facebook':
                $query->where('order_source_id', 1);
                break;
            case 'whatsapp':
                $query->where('order_source_id', 2);
                break;
            case 'call':
                $query->where('order_source_id', 3);
                break;
        }

        if (auth()->user()->warehouse_id) {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }
        //search query
        if (!empty($this->_invoice_no)) {
            $query->where('invoice_no', 'like', '%' . $this->_invoice_no . '%');
        }

        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereBetween('sale_date', [$this->_start_date, $this->_end_date]);
        }

        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id);
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
        $query = self::toBase();
        if (auth()->user()->warehouse_id) {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }
        return $query->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/
}
