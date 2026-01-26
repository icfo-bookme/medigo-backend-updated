<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'value'];
    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        return $this->value ? ('storage/' . LOGO_PATH) : null;
    }

    public static function get($name)
    {
        $setting = new self();
        $entry = $setting->where('name', $name)->first();
        if (!$entry) {
            return;
        }
        return $entry->value;
    }

    public static function set($name, $value = null)
    {
        self::updateOrInsert(['name' => $name], ['name' => $name, 'value' => $value]);
        Config::set('name', $value);
        if (Config::get($name) == $value) {
            return true;
        }
        return false;
    }

     public static function flushCurrentCache(){
   
        DB::table('ecom_caches')->insert([
            ['key' => 'category'],
            ['key' => 'product'],
            ['key' => 'brand']
        ]);
         Cache::forget('_company_info');
    }

    /*************************************
     * * *  Begin :: Cache Data * * *
     **************************************/
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
    /***********************************
     * * *  End :: Cache Data * * *
     ************************************/
}
