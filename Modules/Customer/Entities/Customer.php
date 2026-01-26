<?php

namespace Modules\Customer\Entities;

use Illuminate\Support\Facades\Hash;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Sale\Entities\Sale;
use Modules\Setting\Entities\Warehouse;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Authenticatable implements JWTSubject
{
    protected $fillable = ['warehouse_id', 'name', 'phone', 'email', 'country', 'district', 'city', 'thana', 'area', 'image', 'information', 'optional_information', 'otp', 'password', 'status', 'created_by', 'modified_by', 'remember_token'];

    protected $hidden = ['password', 'remember_token'];
    protected $guard = 'customer';
    protected $with = ['addresses', 'customerPoint'];
    protected $appends = ['image_path'];

    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Hash password when setting it
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function getImagePathAttribute()
    {
        return $this->image ? ('storage/' . CUSTOMER_AVATAR_PATH) : asset('image/default-avatar.png');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withDefault(['name' => '']);
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function customerPoint()
    {
        return $this->hasOne(CustomerPoint::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_id', 'id');
    }

    public function coa()
    {
        return $this->hasOne(ChartOfAccount::class, 'customer_id', 'id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $order = ['id' => 'asc'];

    protected $_search_text, $_sort_table, $_total_count_filtered;
    protected $column_order, $orderValue, $dirValue, $startVlaue, $lengthVlaue;

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

    //methods to set custom search property value
    public function setSearchText($search_text)
    {
        $this->_search_text = $search_text;
    }

    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }

    public function setQueryCount($total_count)
    {
        $this->_total_count_filtered = $total_count;
    }

    private function get_datatable_query()
    {
        $query = self::with('warehouse:id,name', 'addresses', 'customerPoint');

        if (auth()->user()->warehouse_id) {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        //search query
        if (!empty($this->_search_text)) {
            $name = $this->_search_text;

            // Combine relevance score calculation for both name and mobile fields
            $query->where(function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%')
                    ->orWhere('phone', 'like', '%' . $name . '%');
            });

            $query->selectRaw(
                "*, (CASE WHEN name LIKE ? THEN 1 ELSE 0 END +
                 CASE WHEN name LIKE ? THEN 2 ELSE 0 END +
                 CASE WHEN name LIKE ? THEN 3 ELSE 0 END +
                 CASE WHEN phone LIKE ? THEN 1 ELSE 0 END +
                 CASE WHEN phone LIKE ? THEN 2 ELSE 0 END +
                 CASE WHEN phone LIKE ? THEN 3 ELSE 0 END) as relevance",
                ["%{$name}%", "{$name}%", "%{$name}", "%{$name}%", "{$name}%", "%{$name}"]
            );
        }
        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
            }
        }

        // query filter count set
        $this->setQueryCount($query->count());

        //order by relevance score first, then by other order parameters
        if (!empty($this->_search_text)) {
            $query->orderByRaw('relevance DESC');
        }

        //order by data fetching code
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
        return $this->_total_count_filtered;
    }

    public function count_all()
    {
        $query = self::toBase();
        if (auth()->user()->warehouse_id) {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        return $query->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/
}
