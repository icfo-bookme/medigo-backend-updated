<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabourLeaveApp extends BaseModel
{
    protected $table    = 'labour_leave_apps';
    protected $fillable = ['created_by','leave_category','labour_id','start_date','end_date','purpose','date','status'];

    public function leaveCategory()
    {
        return $this->belongsTo(LeaveCategory::class,'leave_category','id');
    }
    public function labour()
    {
        return $this->belongsTo(Labour::class,'labour_id','id');
    }

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
        $query = self::with('leaveCategory','labour');
        //search query
        if (!empty($this->labour_id)) {
            $query->where('labour_id', $this->labour_id);
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
