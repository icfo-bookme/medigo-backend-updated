<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

class CustomerAddress extends BaseModel
{
    protected $fillable = ['customer_id', 'label', 'district', 'city', 'thana', 'area', 'information'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
