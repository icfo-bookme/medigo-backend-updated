<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;

class Shift extends BaseModel
{

    protected $fillable = ['name', 'starting_time', 'ending_time', 'status', 'department', 'created_by', 'modified_by', 'deleted_by', 'shift_hours'];

    protected $table = 'shifts';

    protected $name;
    protected $department;

    //methods to set custom search property value
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDepartment($department)
    {
        $this->department = $department;
    }


    private function get_datatable_query()
    {
        $this->column_order = ['name', 'starting_time', 'ending_time', 'status', 'department', 'created_by', 'modified_by', 'deleted_by', null];


        $query = self::toBase()->where('status', 1);

        if (!empty($this->name)) {
            $query->where('name', 'like', '%' . $this->name . '%');
        }
        if (!empty($this->department)) {
            $query->where('department', $this->department);
        }

        //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table nameer and dirValue is asc or desc
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
        return $query->get()->count();
    }
}
