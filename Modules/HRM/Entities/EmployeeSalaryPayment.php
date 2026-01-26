<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Modules\Account\Entities\ChartOfAccount;

class EmployeeSalaryPayment extends BaseModel
{

    protected $table = 'employee_salary_payments';
    protected $fillable = [
        'date',
        'employee_id',
        'paid_amount',
        'account_id',
        'created_by',
        'modified_by',
        'payment_method',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id', 'id');
    }

    //custom search column property
    protected $_employee_id;

    protected $_status;

    //methods to set custom search property value
    public function setEmployee($employee_id)
    {
        $this->_employee_id = $employee_id;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    private function get_datatable_query()
    {
        $query = self::where('status', '!=', 3)->with('employee', 'coa');

        if (!empty($this->_employee_id)) {
            $query->where('employee_id', $this->_employee_id);
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
