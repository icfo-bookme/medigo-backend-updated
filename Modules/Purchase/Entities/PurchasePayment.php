<?php

namespace Modules\Purchase\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Entities\Purchase;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;

class PurchasePayment extends Model
{
    protected $fillable = ['purchase_id', 'account_id', 'transaction_id','supplier_debit_transaction_id',
     'amount', 'payment_method', 'cheque_no', 'payment_note', 'created_by', 'modified_by'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class,'account_id','id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function supplier_debit_transaction()
    {
        return $this->belongsTo(Transaction::class,'supplier_debit_transaction_id','id');
    }
}
