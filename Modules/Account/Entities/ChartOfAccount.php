<?php

namespace Modules\Account\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use Modules\Supplier\Entities\Supplier;
use Modules\Account\Entities\Transaction;


class ChartOfAccount extends BaseModel{
    protected $fillable = [ 'code', 'name', 'parent_name', 'level', 'type', 'transaction', 'general_ledger', 'customer_id', 'supplier_id', 'bank_id','mobile_bank_id','budget', 'depreciation', 'depreciation_rate', 'status', 'created_by', 'modified_by'];
    protected $table    = 'chart_of_accounts';
    protected $order    = ['id' => 'asc'];
    protected $_name;
    public function transactions(){
        return $this->hasMany(Transaction::class,'chart_of_account_id','id');
    }
    public static function bankHeadCode(){
        return self::where('level',4)->where('code','like', '1020102%')->max('code');
    }
    public static function mobileBankHeadCode(){
        return self::where('level',4)->where('code','like', '1020103%')->max('code');
    }
    public static function account_id_by_name($name){
        $query = self::where('name',$name)->first();
        return $query->id;
    }
    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_id','id');
    }
    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id','id');
    }
    public static function accounts(){
        $accounts = '';
        $accounts .= (new self)->coa('COA');
        return $accounts;
    }
    private function coa($parent_name,$level = 0){
        $module = '';
        if($parent_name == 'COA'){
            $modules = self::where(['parent_name' => 'COA'])->orderBy('code','asc')->get(); //get module list whose parent id is 0
        }else{
            $modules = self::where(['parent_name' => $parent_name])->orderBy('code','asc')->get(); //get module list whose parent id is the given id
        }
        if(!$modules->isEmpty()){
            foreach ($modules as $value) {
                $children = self::where(['parent_name' => $value->name])->get();
                $amount = 0;
                if(count($children) > 0) {
                    foreach ($children as $item) {
                        $amount += $this->children($item);
                    }
                }else{
                    $balance = DB::table('transactions')
                        ->select(DB::raw("SUM(debit) - SUM(credit) as balance"))
                        ->where([['chart_of_account_id',$value->id],['approve',1]])
                        ->first();
                    $amount += !empty($balance) ? $balance->balance : 0;
                }
                $module .= "<option value='".$value->id."'>".str_repeat("&#9866;", $level).' '.$value->name." [ Bl: ".$amount."Tk]";
                $module .= $this->coa($value->name,$level+1);
                $module .= "</option>";
            }
        }
        return $module;
    }
    private function children($item){
        $amount = 0;
        $children = self::where(['parent_name' => $item->name])->get();
        if(count($children) > 0) {
            foreach ($children as $item) {
                $amount += $this->children($item);
            }
        }else{
            $transaction = DB::table('transactions as t')
                ->select(DB::raw("SUM(t.debit) - SUM(t.credit) as balance"))
                ->where([['t.chart_of_account_id',$item->id],['approve',1]])
                ->first();
            $amount += $transaction ? $transaction->balance : 0;
        }
        return $amount;
    }
    public function setName($name){
        $this->_name = $name;
    }
    private function get_datatable_query(){
        $this->column_order = ['id','name', 'code','parent_name','type',null,null,null];
        $query = self::toBase();
        if (!empty($this->_name)) {
            $query->where('name', 'like','%'.$this->_name.'%');
        }
        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table header and dirValue is asc or desc
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue); //fetch data order by matching column
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
        return self::toBase()->get()->count();
    }
}
