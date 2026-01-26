<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Cache;

class Campaign extends BaseModel
{
    protected $fillable = ['campaign_type', 'name', 'slug', 'discount_type', 'discount_amount', 'image', 'start_date', 'end_date', 'status', 'created_by', 'modified_by'];

    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        return $this->image ? ('storage/' . CAMPAIGN_IMAGE_PATH) : asset('img/default.jpg');
    }

    public function products()
    {
        return $this->hasOne(CampaignProduct::class);
    }

    public function categories()
    {
        return $this->hasOne(CampaignCategory::class);
    }

    // Log History
    public function user_activity()
    {
        return $this->morphMany(UserActivity::class, 'logable');
    }

    protected $_name, $_status, $sort_table;

    //methods to set custom search property value

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }


    private function get_datatable_query()
    {
        $query = self::toBase();

        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
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
    /*************************************
     * * *  Begin :: Cache Data * * *
     **************************************/
    protected const CAMPAIGN_CACHE_KEY = 'campaign_cache_key';

    public static function allCampaigns()
    {
        return Cache::rememberForever(self::CAMPAIGN_CACHE_KEY, function () {
            $currentDate = now(); // Get the current date

            return self::where('status', 1)
                ->whereDate('start_date', '<=', $currentDate)  // Check if start_date is in the past or today
                ->whereDate('end_date', '>=', $currentDate)    // Check if end_date is in the future or today
                ->get();
        });
    }

    public static function getCampaignsByType($type)
    {
        return Cache::rememberForever(self::CAMPAIGN_CACHE_KEY . '_' . $type, function () use ($type) {
            return self::where('status', 1)->where('campaign_type', $type)->get();
        });
    }

    public static function flushCache($type = null)
    {
        Cache::forget(self::CAMPAIGN_CACHE_KEY);

        self::allCampaigns();

        if ($type) {
            Cache::forget(self::CAMPAIGN_CACHE_KEY . '_' . $type);
            self::getCampaignsByType($type);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($campaign) {
            self::flushCache($campaign->campaign_type);
        });

        static::deleted(function ($campaign) {
            self::flushCache($campaign->campaign_type);
        });
    }
    /*************************************
     * * *  End :: Cache Data * * *
     **************************************/
}
