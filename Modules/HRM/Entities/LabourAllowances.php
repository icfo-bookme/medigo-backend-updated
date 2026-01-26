<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabourAllowances extends BaseModel
{
    protected $table = 'allowances';
    protected $fillable = ['created_by','employee_id','allowances_name','allowances_month','allowances_amount','allowances_description','status','type'];

    public function labour()
    {
        return $this->belongsTo(Labour::class,'employee_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $allowances_name;

    //methods to set custom search property value
    public function setName($allowances_name)
    {
        $this->allowances_name = $allowances_name;
    }

    private function get_datatable_query()
    {
        $query = self::where('type',2)->with('labour');

        //search query
        if (!empty($this->allowances_name)) {
            $query->where('allowances_name', 'like', '%' . $this->allowances_name . '%');
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
