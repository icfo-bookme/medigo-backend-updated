<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class Labour extends BaseModel
{
    protected $table = 'labours';
    protected $fillable = ['labour_id','name','gender','present_address','joining_date','joining_month','id_number','contact_no_one','emergency_contact','date_of_birth','marital_status','branch_id','blood_group','resume','basic_salary','status','retired_date','created_by','date','modified_by'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $labour_id;

    //methods to set custom search property value
    public function setName($labour_id)
    {
        $this->labour_id = $labour_id;
    }

    private function get_datatable_query()
    {
        $query = self::toBase();

        //search query
        if (!empty($this->labour_id)) {
            $query->where('labour_id', 'like', '%' . $this->labour_id . '%');
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
