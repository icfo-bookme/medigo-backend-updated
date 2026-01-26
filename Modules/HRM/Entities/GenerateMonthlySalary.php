<?php

namespace Modules\HRM\Entities;


use App\Models\BaseModel;

class GenerateMonthlySalary extends BaseModel
{

    protected $table = 'generate_monthly_salaries';
    protected $fillable = ['employee_id', 'shift_id', 'month', 'year', 'total_working_days', 'total_holidays', 'total_attended', 'total_paid_leaves', 'total_unpaid_leaves', 'total_working_hours', 'total_attended_hours', 'increment_salary', 'current_salary', 'gross_salary', 'net_salary', 'created_by', 'modified_by', 'approved_by', 'deleted_by', 'status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    //custom search column property
    protected $_employee_id;
    protected $_month;
    protected $_year;
    protected $_status;

    //methods to set custom search property value
    public function setEmployee($employee_id)
    {
        $this->_employee_id = $employee_id;
    }

    public function setMonth($month)
    {
        $this->_month = $month;
    }

    public function setYear($year)
    {
        $this->_year = $year;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    private function get_datatable_query()
    {
        $query = self::where('status', '!=', 3)->with('employee', 'shift');

        if (!empty($this->_employee_id)) {
            $query->where('employee_id', $this->_employee_id);
        }
        if (!empty($this->_month)) {
            $query->where('month', $this->_month);
        }
        if (!empty($this->_year)) {
            $query->where('year', $this->_year);
        }
        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
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
