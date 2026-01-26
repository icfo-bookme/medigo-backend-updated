<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabourAdvance extends BaseModel
{
    protected $table = 'labour_advances';
    protected $fillable = ['created_by','labour_id','advance_month','advance_amount','advance_description','status'];

    public function labour()
    {
        return $this->belongsTo(Labour::class,'labour_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $labour_id;
    protected $advance_month;

    //methods to set custom search property value
    public function setName($labour_id)
    {
        $this->labour_id = $labour_id;
    }
    public function setMonth($advance_month)
    {
        $this->advance_month = $advance_month;
    }

    private function get_datatable_query()
    {
        $query = self::with('labour');

        //search query
        if (!empty($this->labour_id)) {
            $query->where('labour_id', $this->labour_id );
        }
        if (!empty($this->advance_month)) {
            $query->where('advance_month', $this->advance_month);
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
