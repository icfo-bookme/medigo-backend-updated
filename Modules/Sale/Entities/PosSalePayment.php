<?php

namespace Modules\Sale\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Account\Entities\ChartOfAccount;

class PosSalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'paid_amount', 'account_id', 'reference_no', 'payment_method'
    ];

    protected $table = 'pos_sale_payments';

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'account_id','id');
    }

}
