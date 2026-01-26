<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;

class Draft extends BaseModel
{
    protected $table = 'drafts';
    protected $fillable = ['draft_no', 'amount', 'description', 'created_by', 'modified_by', 'status'];

    protected $_invoice_no;
    protected $_status;

    //methods to set custom search property value
    public function setInvoiceNo($draft_no)
    {
        $this->_draft_no = $draft_no;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }


    private function get_datatable_query()
    {
        $query = self::toBase();


        if (!empty($this->_draft_no)) {
            $query->where('draft_no', 'like', '%' . $this->_draft_no . '%');
        }
        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
        }
        if (isset($this->orderValue) && isset($this->dirValue)) {
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue);
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
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
}
