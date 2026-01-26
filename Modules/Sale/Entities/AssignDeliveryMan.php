<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignDeliveryMan extends BaseModel
{
    use HasFactory;

    protected $fillable = ['invoice_no','sale_id','delivery_man_id','date'];
    protected $table    = 'assign_delevery_man';

    public function sale()
    {
        return $this->belongsTo(Sale::class,'sale_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'delivery_man_id','id');
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
        $query = self::with('sale');

        //search query
        if (!empty($this->_invoice_no)) {
            $query->where('invoice_no', $this->_invoice_no);
        }
        if (!empty($this->delivery_man_id)) {
            $query->where('delivery_man_id', $this->delivery_man_id);
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
        $query = self::toBase();
        return $query->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/
}
