<?php

namespace Modules\Sale\Entities;


use App\Models\BaseModel;

class OrderWithCall extends BaseModel
{

    protected $fillable = ['facebook','whatsapp','mobile','status','created_by','modified_by','created_at','updated_at'];

    protected $table = 'order_with_calls';

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/


    //custom search column property
    protected $status;
    protected $created_at;

    //methods to set custom search property value



    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['name','id','link_name','user_address','status','created_by','modified_by',null];

        $query = self::toBase();


        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        if (!empty($this->created_at)) {
            $query->where('created_at', $this->created_at);
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
        $query = self::toBase();
        return $query->count();
    }
}

