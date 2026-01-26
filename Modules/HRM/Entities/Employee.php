<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Hash;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Department\Entities\Department;
use Modules\Designation\Entities\Designation;

class Employee extends BaseModel
{
    protected $table = 'employees';
    protected $fillable = ['user_id', 'employee_id', 'name', 'username', 'email', 'gender', 'father_name', 'mother_name', 'spouse_name', 'present_address', 'permanent_address', 'home_district', 'academic_qualification', 'professional_qualification', 'joining_date', 'joining_month', 'experience', 'reference', 'id_name', 'id_number', 'contact_no_one', 'contact_no_two', 'emergency_contact', 'web', 'date_of_birth', 'marital_status', 'branch_id', 'designation_id', 'department_id', 'activation_status', 'blood_group', 'deletion_status', 'resume', 'status', 'retired_date', 'created_by', 'date', 'modified_by', 'previous_workplace'];

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function salary()
    {
        return $this->hasOne(Salary::class, 'employee_id', 'id');
    }

    public function coa()
    {
        return $this->hasOne(ChartOfAccount::class, 'employee_id', 'id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    //custom search column property
    protected $_employee_id;

    //methods to set custom search property value
    public function setName($employee_id)
    {
        $this->_employee_id = $employee_id;
    }

    private function get_datatable_query()
    {
        $query = self::toBase();

        //search query
        if (!empty($this->_employee_id)) {
            $query->where('employee_id', 'like', '%' . $this->_employee_id . '%');
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
