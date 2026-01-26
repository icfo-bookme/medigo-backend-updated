<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class EmployeeLeave extends BaseModel
{

    protected $fillable = ['employee_id', 'leave_category_id', 'status', 'start_date', 'end_date', 'duration', 'notes', 'created_by', 'modified_by', 'approved_by', 'deleted_by', 'is_paid'];


    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function leaveCategory()
    {
        return $this->belongsTo(LeaveCategory::class, 'leave_category_id', 'id');
    }


    protected $order = ['id' => 'desc'];
    protected $_employee_id;
    protected $_leave_category_id;
    protected $_from_date;
    protected $_to_date;
    protected $_type;

    public function setEmployee($employee_id)
    {
        $this->_employee_id = $employee_id;
    }

    public function setLeaveCategory($leave_category_id)
    {
        $this->_leave_category_id = $leave_category_id;
    }

    public function setFromDate($from_date)
    {
        $this->_from_date = $from_date;
    }

    public function setToDate($to_date)
    {
        $this->_to_date = $to_date;
    }

    public function setType($type)
    {
        $this->_type = $type;
    }

    private function get_datatable_query()
    {
        $this->column_order = ['employee_id', 'leave_category_id', 'status', 'start_date', 'end_date', 'duration', 'notes', 'created_by', 'modified_by', 'approved_by', 'deleted_by', 'is_paid', null];
        $query = self::with('employee', 'leaveCategory')->where('status', '!=', '3');
        if (!empty($this->_employee_id)) {
            $query->where('employee_id', $this->_employee_id);
        }
        if (!empty($this->_leave_category_id)) {
            $query->where('leave_category_id', $this->_leave_category_id);
        }
        if (!empty($this->_from_date)) {
            $query->whereDate('start_date', '>=', $this->_from_date);
        }
        if (!empty($this->_to_date)) {
            $query->whereDate('start_date', '<=', $this->_to_date);
        }

        if (!empty($this->_type)) {
            $query->where('status', $this->_type);
        }
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
        return $query->get()->count();
    }

    public function count_all()
    {
        return DB::table('employee_leaves')->get()->count();
    }
}
