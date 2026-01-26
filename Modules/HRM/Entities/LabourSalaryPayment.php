<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabourSalaryPayment extends BaseModel
{
    protected $table    = 'labour_salary_payment';
    protected $fillable = ['labour_id','created_by','basic_salary','total_working_days','avg_overtime_hour','overtime_amount','total_gross_salary','avg_absence_hour','absence_amount','salary_advance','other_deduction','total_deduction','payable_salary','net_payable','bonus','payment_month','payment_type','note','created_at'];

    public function labour()
    {
        return $this->belongsTo(Labour::class,'labour_id','id');
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
        return $this->belongsTo(LabourAdvance::class,'employee_id','employee_id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $labour_id;
    protected $payment_month;

    //methods to set custom search property value
    public function setName($labour_id)
    {
        $this->labour_id = $labour_id;
    }
    public function setMonth($payment_month)
    {
        $this->payment_month = $payment_month;
    }

    private function get_datatable_query()
    {
        $query = self::with('labour');

        //search query
        if (!empty($this->labour_id)) {
            $query->where('labour_id', $this->labour_id);
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
