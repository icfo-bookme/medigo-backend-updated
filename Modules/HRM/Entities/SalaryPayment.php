<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryPayment extends BaseModel
{
    protected $table = 'salary_payments';
    protected $fillable = ['created_by','employee_id','basic_salary','gross_salary','salary_advance','total_deduction','total_allowance','bonus','net_salary','provident_fund','payment_amount','payment_month','payment_type','note'];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
    public function salary()
    {
        return $this->belongsTo(Salary::class,'employee_id','employee_id');
    }
    public function advance_salary()
    {
        return $this->belongsTo(Advance::class,'employee_id','employee_id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $employee_id;
    protected $payment_month;

    //methods to set custom search property value
    public function setName($employee_id)
    {
        $this->employee_id = $employee_id;
    }
    public function setMonth($payment_month)
    {
        $this->payment_month = $payment_month;
    }

    private function get_datatable_query()
    {
        $query = self::with('employee');

        //search query
        if (!empty($this->employee_id)) {
            $query->where('employee_id', $this->employee_id);
        }
        if (!empty($this->payment_month)) {
            $query->where('payment_month', $this->payment_month);
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
    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/
}
