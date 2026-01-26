<?php

namespace Modules\HRM\Entities;

use App\Http\Controllers\BaseController;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveCategory extends BaseModel
{
    protected $table = 'leave_categories';
    protected $fillable = ['created_by', 'leave_category', 'leave_category_description', 'status', 'deleted_by', 'modified_by'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $_name;

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }

    private function get_datatable_query()
    {
        $query = self::toBase()->where('status', 1);
        //search query
        if (!empty($this->_name)) {
            $query->where('leave_category', 'like', '%' . $this->_name . '%');
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
        return $query->where('status', 1)->get()->count();
    }

    public function count_all()
    {
        return self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/
}
