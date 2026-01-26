<?php

namespace Modules\Sale\Entities;

use App\Events\PusherBroadcast;
use App\Models\BaseModel;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Coupon\Entities\Coupon;
use Modules\Product\Entities\Product;
use Modules\Customer\Entities\Customer;
use Modules\Setting\Entities\Warehouse;

class Sale extends BaseModel
{
    protected $fillable = ['invoice_no', 'warehouse_id', 'customer_id', 'ecom_customer_id', 'item', 'total_qty', 'total_return_qty',
        'total_discount', 'total_tax', 'total_price', 'order_tax_rate', 'order_tax', 'order_discount_per', 'order_discount',
        'shipping_cost', 'order_discount_rate', 'grand_total', 'adjustment_per', 'adjustment', 'net_total', 'paid_amount', 'change_amount',
        'payment_method', 'order_source_id', 'payment_status', 'account_id', 'reference_no', 'sale_date', 'delivery_status', 'delivery_date',
        'est_delivery_date', 'coupon_id', 'coupon_discount_value', 'sale_type', 'order_type', 'name', 'phone', 'information',
        'optional_information', 'created_by', 'modified_by', 'date_wise_serial'
    ];
    protected $table = 'sales';
    protected $with = ['saleProductList'];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(SaleProduct::class, 'sale_products', 'sale_id', 'sale_id');
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(PosSalePayment::class, 'pos_sale_payments', 'sale_id', 'sale_id');
    }

    public function pos_payments()
    {
        return $this->hasMany(PosSalePayment::class, 'sale_id', 'id');
    }

    public function saleProductList()
    {
        return $this->hasMany(SaleProduct::class, 'sale_id', 'id');
    }

    public function sale_products()
    {
        return $this->hasMany(SaleProduct::class)->where('qty', '>', 0);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function order_customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->where('status', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function assignDeliveryMen()
    {
        return $this->hasMany(AssignDeliveryMan::class, 'sale_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'sale_products', 'sale_id', 'product_id', 'id', 'id')
            ->withPivot('id', 'sale_id', 'serial_no', 'qty', 'sale_unit_id', 'net_unit_price', 'discount', 'discount_rate', 'tax_rate', 'tax', 'total')
            ->where('qty', '>', 0)
            ->withTimestamps();
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

    public function user_activity()
    {
        return $this->morphMany(UserActivity::class, 'logable');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
     *******************************************/
    //custom search column property
    protected $_invoice_no, $_start_date, $_end_date, $_warehouse_id, $_customer_id, $_order_source_id, $_created_by, $_sort_table
    , $_delivery_status, $_search_field, $_order_type;

    //methods to set custom search property value
    public function setInvoiceNo($invoice_no)
    {
        $this->_invoice_no = $invoice_no;
    }

    public function setOrderSource($order_source_id)
    {
        $this->_order_source_id = $order_source_id;
    }

    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }

    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }

    public function setTableOrder($sort_table)
    {
        $this->_sort_table = $sort_table;
    }

    public function setCreatedBy($created_by)
    {
        $this->_created_by = $created_by;
    }

    public function setSearchField($search_field)
    {
        $this->_search_field = $search_field;
    }

    public function setDeliveryStatus($delivery_status)
    {
        $this->_delivery_status = $delivery_status;
    }

    public function setOrderType($order_type)
    {
        $this->_order_type = $order_type;
    }

    private function get_datatable_query()
    {
        $query = self::with('customer', 'order_customer', 'user_activity', 'user_activity.user:id,name,username,phone');

        //search query
        $query->when(!empty($this->_search_field), function ($query) {
            $query->where(function ($query) {
                $query->where('invoice_no', 'like', '%' . $this->_search_field . '%')
                    ->orWhereHas('customer', function ($query) {
                        $query->where('name', 'like', '%' . $this->_search_field . '%')
                            ->orWhere('phone', 'like', '%' . $this->_search_field . '%');
                    })
                    ->orWhere('phone', 'like', '%' . $this->_search_field . '%');
            });
        });

        if (!empty($this->_invoice_no)) {
            $query->where('invoice_no', 'like', '%' . $this->_invoice_no . '%');
        }
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereBetween('sale_date', [$this->_start_date, $this->_end_date]);
        }
        if (!empty($this->_customer_id)) {
            $query->where('customer_id', $this->_customer_id);
        }
        if (!empty($this->_order_source_id)) {
            $query->where('order_source_id', $this->_order_source_id);
        }
        if (!empty($this->_delivery_status)) {
            $query->where('delivery_status', $this->_delivery_status);
        }
        if (!empty($this->_order_type)) {
            $query->where('order_type', $this->_order_type);
        }


        if (!empty($this->_sort_table)) {
            if ($this->_sort_table == 'latest') {
                $query->orderBy('id', 'desc');
            } else if ($this->_sort_table == 'oldest') {
                $query->orderBy('id', 'asc');
            } else if ($this->_sort_table == 'pending') {
                $query->where('delivery_status', 1);
            }
        }
        if (!empty($this->_created_by)) {
            $query->where('created_by', $this->_created_by);
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
        return $this->get_datatable_query()->count();
    }

    public function count_all()
    {
        return self::count();
    }

    /******************************************
     * * * End :: Custom Datatable Code * * *
     *******************************************/

    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
//            SaleNotification::create([
//                'sale_id'=> $model->id,
//                'invoice' => $model->invoice_no,
//                'order_source' => $model->order_source_id,
//                'is_seen' => 0
//            ]);
            $message = 'New Order Created';
            broadcast(new PusherBroadcast($message))->toOthers();
        });
    }
}
