<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;

class DailyAttendance extends BaseModel
{

    protected $table = 'daily_attendances';
    protected $fillable = ['employee_id', 'shift_id', 'check_in_date', 'check_in_time', 'check_out_date', 'check_out_time', 'working_hour', 'dept_type', 'approval_status', 'type', 'approve_remarks', 'approved_by'];

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }


    protected $_employee_id;
    protected $_shift_id;
    protected $_from_date;
    protected $_to_date;
    protected $_status;

    //methods to set custom search property value
    public function setEmployee($employee_id)
    {
        $this->_employee_id = $employee_id;
    }

    public function setShift($shift_id)
    {
        $this->_shift_id = $shift_id;
    }

    public function setFromDate($from_date)
    {
        $this->_from_date = $from_date;
    }

    public function setToDate($to_date)
    {
        $this->_to_date = $to_date;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    private function get_datatable_query()
    {
        $query = self::with('employee', 'shift');

        if (!empty($this->_employee_id)) {
            $query->where('employee_id', $this->_employee_id);
        }
        if (!empty($this->_shift_id)) {
            $query->where('shift_id', $this->_shift_id);
        }
        if (!empty($this->_from_date)) {
            $query->where('check_in_date', '>=', $this->_from_date);
        }
        if (!empty($this->_to_date)) {
            $query->where('check_in_date', '<=', $this->_to_date);
        }
        if (!empty($this->_status)) {
            $query->where('approval_status', $this->_status);
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
