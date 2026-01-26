<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

class WelcomeCall extends BaseModel
{
    protected $fillable = ['customer_id', 'name', 'phone', 'email', 'approved_by', 'call_status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property.
    protected $_search_text, $_sort_table, $_call_status, $_total_count_filtered;

    //methods to set custom search property value
    public function setSearchText($search_text)
    {
        $this->_search_text = $search_text;
    }

    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }

    public function setCallStatus($call_status)
    {
        $this->_call_status = $call_status;
    }

    public function setQueryCount($total_count)
    {
        $this->_total_count_filtered = $total_count;
    }

    private function get_datatable_query()
    {
        $query = self::with('customer')
            ->when($this->_call_status, function ($q) {
                $q->where('call_status', $this->_call_status);
            }, function ($q) {
                $q->where('call_status', '!=', 2);
            });

        //search query
        if (!empty($this->_search_text)) {
            $name = $this->_search_text;
            $query->whereHas('customer', function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%')
                    ->orWhere('phone', 'like', '%' . $name . '%');
            });

            $query->selectRaw(
                "*, (CASE WHEN name LIKE ? THEN 1 ELSE 0 END +
                 CASE WHEN name LIKE ? THEN 2 ELSE 0 END +
                 CASE WHEN name LIKE ? THEN 3 ELSE 0 END +
                 CASE WHEN phone LIKE ? THEN 1 ELSE 0 END +
                 CASE WHEN phone LIKE ? THEN 2 ELSE 0 END +
                 CASE WHEN phone LIKE ? THEN 3 ELSE 0 END) as relevance",
                ["%{$name}%", "{$name}%", "%{$name}", "%{$name}%", "{$name}%", "%{$name}"]
            );
            //order by relevance score first, then by other order parameters
            $query->orderByRaw('relevance DESC');
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
        return self::count();
    }
}
