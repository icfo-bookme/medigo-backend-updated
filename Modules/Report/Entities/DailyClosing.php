<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;

class DailyClosing extends BaseModel
{
    protected $fillable = ['date', 'title', 'warehouse_id', 'closing_amount', 'created_by', 'modified_by', 'note'];

    protected $table = 'daily_closings';
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withDefault(['name' => '']);
    }

    public function dailyClosingHeads()
    {
        return $this->hasMany(DailyClosingHead::class, 'daily_closing_id','id');
    }

    public function allHeads(){
        return $this->belongsToMany(DailyClosingHead::class,'daily_closing_heads','daily_closing_id','closing_head_id')->withTimestamps();
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property

    protected $_start_date, $_end_date, $_warehouse_id;

    //methods to set custom search property value
    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }

    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    private function get_datatable_query()
    {
        $query = self::with('warehouse');
        if (optional(Auth::user())->warehouse_id) {
            $query->where('warehouse_id', optional(Auth::user())->warehouse_id);
        }
        //search query
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereBetween('date', [$this->_start_date, $this->_end_date]);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id);
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
        return $this->get_datatable_query()->count();
    }

    public function count_all()
    {
        $query = self::toBase();
        if (optional(Auth::user())->warehouse_id) {
            $query->where('warehouse_id', optional(Auth::user())->warehouse_id);
        }

        return $query->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/
}
