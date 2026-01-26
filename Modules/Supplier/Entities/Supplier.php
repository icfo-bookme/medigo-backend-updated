<?php

namespace Modules\Supplier\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;

class Supplier extends BaseModel
{
    protected $fillable = [ 'name', 'company_name', 'mobile', 'email', 'phone', 'city', 'zipcode', 'address', 'status', 'created_by', 'modified_by'];

    public function coa(){
        return $this->hasOne(ChartOfAccount::class,'supplier_id','id');
    }

    public function previous_balance()
    {
        return $this->hasOneThrough(Transaction::class,ChartOfAccount::class,'supplier_id','chart_of_account_id','id','id')
        ->where('voucher_type','PR Balance')->withDefault(['debit' => '']);
    }

    public function supplier_balance(int $id)
    {
        $data = DB::table('suppliers as s')
            ->selectRaw('s.id,b.id as coaid,b.code,((select ifnull(sum(debit),0) from transactions where chart_of_account_id= b.id AND approve = 1)-(select ifnull(sum(credit),0) from transactions where chart_of_account_id= b.id AND approve = 1)) as balance')
            ->leftjoin('chart_of_accounts as b', 's.id', '=', 'b.supplier_id')
            ->where('s.id',$id)->first();
        $balance = 0;
        if($data)
        {
            $balance = $data->balance ? $data->balance : 0;
        }
        return $balance;
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_search_text, $_sort_table;

    //methods to set custom search property value
    public function setEntry($search_text)
    {
        $this->_search_text = $search_text;
    }
    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }

    private function get_datatable_query()
    {
        $query = self::toBase();

        //search query
        if (!empty($this->_search_text)) {
            $query->where('name', 'like', '%' . $this->_search_text . '%');
            $query->orWhere('mobile', 'like', '%' . $this->_search_text . '%');
        }

        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
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
        return self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

     /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/
    public function scopeActive($query)
    {
        return $query->where(['status'=>1]);
    }

    public function scopeInactive($query)
    {
        return $query->where(['status'=>2]);
    }
    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_SUPPLIERS    = '_suppliers';

    public static function allSuppliers(){
        // return Cache::rememberForever(self::ALL_SUPPLIERS, function () {
            return self::toBase()->where('status',1)->orderBy('name','asc')->get();
        // });
    }

    public static function flushCache(){
        Cache::forget(self::ALL_SUPPLIERS);
    }


    public static function boot(){
        parent::boot();

        static::updated(function () {
            self::flushCache();
        });

        static::created(function() {
            self::flushCache();
        });

        static::deleted(function() {
            self::flushCache();
        });
    }
    /***********************************
    * * *  Begin :: Cache Data * * *
    ************************************/
}
