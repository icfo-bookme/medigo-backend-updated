<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class Holiday extends BaseModel
{

    protected $fillable = ['type', 'title', 'start_date', 'end_date', 'description', 'created_by'];


    protected $order = ['id' => 'desc'];
    protected $_title;
    protected $_from_date;
    protected $_to_date;
    protected $_type;
    public function setTitle($title){
        $this->_title = $title;
    }
    public function setFromDate($from_date){
        $this->_from_date = $from_date;
    }
    public function setToDate($to_date){
        $this->_to_date = $to_date;
    }
    public function setType($type){
        $this->_type = $type;
    }

    private function get_datatable_query(){
        $this->column_order = ['type', 'title', 'start_date', 'end_date', 'description', 'created_by', null];
        $query = self::toBase();
        if (!empty($this->_title)) {
            $query->where('title', $this->_title);
        }
        if (!empty($this->_from_date)) {
            $query->whereDate('start_date', '>=',$this->_from_date);
        }
        if (!empty($this->_to_date)) {
            $query->whereDate('start_date', '<=', $this->_to_date);
        }

        if (!empty($this->_type)) {
            $query->where('type', $this->_type);
        }
        if (isset($this->orderValue) && isset($this->dirValue)) {
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue);
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
        }
        return $query;
    }
    public function getDatatableList(){
        $query = $this->get_datatable_query();
        if ($this->lengthVlaue != -1) {
            $query->offset($this->startVlaue)->limit($this->lengthVlaue);
        }
        return $query->get();
    }
    public function count_filtered(){
        $query = $this->get_datatable_query();
        return $query->get()->count();
    }
    public function count_all(){
        return DB::table('holidays')->get()->count();
    }
}
