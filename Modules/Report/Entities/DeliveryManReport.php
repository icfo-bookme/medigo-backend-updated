<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\Sale;

class DeliveryManReport extends BaseModel
{
    protected $fillable = ['invoice_no', 'sale_id', 'delivery_man_id', 'date'];

    protected $table = 'assign_delevery_man';

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'delivery_man_id', 'id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $_invoice_no, $delivery_man_id, $_sort_table;

    //methods to set custom search property value
    public function setInvoiceNo($invoice_no)
    {
        $this->_invoice_no = $invoice_no;
    }
    public function setDelivery_man_id($delivery_man_id)
    {
        $this->delivery_man_id = $delivery_man_id;
    }
    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }

    private function get_datatable_query()
    {
        $query = self::select([
            'delivery_man_id',
            DB::raw('SUM(CASE WHEN sales.delivery_status = 5 THEN 1 ELSE 0 END) as delivered_count'),
            DB::raw('SUM(CASE WHEN sales.delivery_status = 6 THEN 1 ELSE 0 END) as cancel_count'),
            DB::raw('MAX(assign_delevery_man.date) as date'), // Use MAX or MIN based on your requirement
            'users.*',
        ])
            ->join('sales', 'assign_delevery_man.sale_id', '=', 'sales.id')
            ->join('users', 'assign_delevery_man.delivery_man_id', '=', 'users.id')
            ->groupBy('delivery_man_id', 'users.name')
            ->orderBy('delivery_man_id', 'desc');

        // Search query
        if (!empty($this->_invoice_no)) {
            $query->where('invoice_no', $this->_invoice_no);
        }
        if (!empty($this->delivery_man_id)) {
            $query->where('delivery_man_id', $this->delivery_man_id);
        }
        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('assign_delevery_man.id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('assign_delevery_man.id', 'asc');
            }
        }

        // Order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) {
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue);
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

        // Remove any ambiguous 'id' ordering for count queries
        $query->getQuery()->orders = null;

        return $query->count();
    }

    public function count_all()
    {
        return self::count();
    }
    /******************************************
 * * * End :: Custom Datatable Code * * *
 *******************************************/
}
