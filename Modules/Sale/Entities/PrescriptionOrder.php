<?php

namespace Modules\Sale\Entities;


use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class PrescriptionOrder extends BaseModel
{
    protected $fillable = ['id','user_id','prescription_file','name','phone','address','otp','status','created_by','modified_by','created_at','updated_at'];

    protected $table = 'prescription_orders';

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/


    //custom search column property
    protected $user_id;
    protected $status;
    protected $created_at , $_mobile_no , $_sort_table;

    //methods to set custom search property value

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }
    public function setMobileNo($mobile_no)
    {
        $this->_mobile_no = $mobile_no;
    }
    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['id','name','created_at','status','created_by','modified_by',null];

        $query = self::with('user');

        if (!empty($this->_mobile_no)) {
            $query->where('phone', $this->_mobile_no);
        }
        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        if (!empty($this->created_at)) {
            $query->where('created_at', $this->created_at);
        }

        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
            } else if ($this->_sort_table == 'pending') {
                $query->where('delivery_status', 1);
            }
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

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

}

