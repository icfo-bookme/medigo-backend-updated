<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;

class Allowance extends BaseModel
{

    protected $fillable = ['head', 'department', 'type', 'status', 'created_by', 'modified_by'];

    protected $table = 'allowance_deductions';

    protected $head;
    protected $department;

    //methods to set custom search property value
    public function setHead($head)
    {
        $this->head = $head;
    }

    public function setDepartment($department)
    {
        $this->department = $department;
    }


    private function get_datatable_query()
    {
        $this->column_order = ['head', 'department', 'type', 'status', 'created_by', 'modified_by', null];


        $query = self::toBase()->where('type',1)->where('status', 1);

        if (!empty($this->head)) {
            $query->where('head', 'like', '%' . $this->head . '%');
        }
        if (!empty($this->department)) {
            $query->where('department', $this->department);
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
        if (!empty($this->type)) {
            $query->where('type',1);
        }
        return $query->get()->count();
    }
}
