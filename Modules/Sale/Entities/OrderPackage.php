<?php

namespace Modules\Sale\Entities;


use App\Models\BaseModel;
use http\Client\Curl\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Customer\Entities\Customer;
use Tymon\JWTAuth\Claims\Custom;

class OrderPackage extends BaseModel
{

    protected $fillable = [
                         'name','user_id',"start_date", "delivery_date",
                         "auto_order_after_days","day_counter","status","item",'total_discount',
                         'net_total','total_qty','shipping_cost',
                         'package_date','grand_total', 'created_by','modified_by','created_at','updated_at'];

    protected  $table = 'order_packages';
    //custom search column property
    protected $user_id;
    protected $status;
    protected $created_at;

    protected  $with = ['customer:id,name,phone,information,optional_information'];

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


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['id','name','created_at','status','created_by','modified_by',null];

        $query = self::toBase();

        if (!empty($this->user_id)) {
            $query->where('user_id', $this->user_id);
        }
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

    public function products(): BelongsToMany {

        return $this->belongsToMany(OrderPackageProducts::class, 'order_package_products','order_package_id','order_package_id');

    }

    public function productsList() {

    return $this->hasMany(OrderPackageProducts::class,'order_package_id','id');

}
    public function customer()
    {
        return $this->belongsTo(Customer::class,'user_id','id');
    }



}

