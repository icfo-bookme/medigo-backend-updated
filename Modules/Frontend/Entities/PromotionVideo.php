<?php
namespace Modules\Frontend\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PromotionVideo extends BaseModel
{
    protected $fillable = ['title', 'url'];

    private function get_datatable_query()
    {
        $query = self::toBase();

                                                                                  //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) {                 //orderValue is the index number of table header and dirValue is asc or desc
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

    /*************************************
     * * *  Begin :: Cache Data * * *
     **************************************/

    protected const PROMOTION_VIDEO = '_promotion_video';

    public static function allPromotionVideo()
    {
        return Cache::rememberForever(self::PROMOTION_VIDEO, function () {
            return self::all();
        });
    }

    public static function flushCurrentCache()
    {

        DB::table('ecom_caches')->insert([
            ['key' => 'category'],
            ['key' => 'product'],
            ['key' => 'brand'],
        ]);
        Cache::forget('_company_info');

        Cache::forget(self::PROMOTION_VIDEO);

        self::allPromotionVideo();

    }

    protected static function booted()
    {
        static::updated(function () {
            self::flushCurrentCache();
        });

        static::created(function () {
            self::flushCurrentCache();
        });

        static::deleted(function () {
            self::flushCurrentCache();
        });
    }
}
