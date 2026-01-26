<?php

namespace Modules\Exchange\Entities;

use App\Models\BaseModel;
use Modules\Account\Entities\ChartOfAccount;

class ExchangeSalePayment extends BaseModel
{
    protected $fillable = ['exchange_id', 'account_id', 'reference_no', 'payment_method', 'paid_amount'];

    public function exchange()
    {
        return $this->belongsTo(Exchange::class, 'exchange_id');
    }

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'account_id','id');
    }
}
