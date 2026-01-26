<?php

namespace App\Models;


use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Setting\Entities\Warehouse;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'warehouse_id','role_id','name','username','email','phone','avatar','gender','password','information','optional_information','status','otp','deletable','created_by','modified_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /***************************************
     * * * Begin :: Model Relationship * * *
    ****************************************/
    public function setPasswordAttribute($value){
        $this->attributes['password'] = Hash::make($value);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withDefault(['name'=>'']);
    }
     /***************************************
     * * * End :: Model Relationship * * *
    ****************************************/

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['id' => 'desc'];
    protected $column_order;

    protected $orderValue;
    protected $dirValue;
    protected $startVlaue;
    protected $lengthVlaue;

    public function setOrderValue($orderValue)
    {
        $this->orderValue = $orderValue;
    }
    public function setDirValue($dirValue)
    {
        $this->dirValue = $dirValue;
    }
    public function setStartValue($startVlaue)
    {
        $this->startVlaue = $startVlaue;
    }
    public function setLengthValue($lengthVlaue)
    {
        $this->lengthVlaue = $lengthVlaue;
    }

    protected $_search_text, $_role_id, $_warehouse_id, $_sort_table;

    public function setSearchText($search_text)
    {
        $this->_search_text = $search_text;
    }
    public function setRoleID($role_id)
    {
        $this->_role_id = $role_id;
    }
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }
    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }


    private function get_datatable_query()
    {
        $query = self::with(['role:id,role_name','warehouse:id,name'])->where('id','!=',1)->where('role_id','!=',3);

        if (!empty($this->_search_text)) {
            $query->where('name', 'like', '%' . $this->_search_text . '%')
                ->orWhere('username', 'like', '%' . $this->_search_text . '%')
                ->orWhere('email', 'like', '%' . $this->_search_text . '%')
                ->orWhere('phone', 'like', '%' . $this->_search_text . '%');
        }
        if (!empty($this->_role_id)) {
            $query->where('role_id', $this->_role_id );
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id );
        }
        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
            }
        }

        if (isset($this->orderValue) && isset($this->dirValue)) {
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue);
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
        return self::where('id','!=',1)->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

}
