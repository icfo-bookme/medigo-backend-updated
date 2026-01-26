<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

class CustomerPoint extends BaseModel
{
    protected $fillable = ['customer_id', 'available_point', 'min_use_point', 'conversion_rate'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
