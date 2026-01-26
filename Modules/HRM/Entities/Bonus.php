<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bonus extends BaseModel
{
    protected $table = 'bonuses';
    protected $fillable = ['created_by','employee_id','bonus_name','bonus_month','bonus_percentage','bonus_description','status','type'];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $bonus_name;

    //methods to set custom search property value
    public function setName($bonus_name)
    {
        $this->bonus_name = $bonus_name;
    }

    private function get_datatable_query()
    {
        $query = self::where('type',1)->with('employee');

        //search query
        if (!empty($this->bonus_name)) {
            $query->where('bonus_name', 'like', '%' . $this->bonus_name . '%');
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
