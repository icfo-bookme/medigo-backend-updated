<?php
namespace Modules\Frontend\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Privacy extends Model
{
    use HasFactory;

    protected $table = 'privacies';

    protected $fillable = ['details'];

    public static function flushCurrentCache()
    {

        DB::table('ecom_caches')->insert([
            ['key' => 'category'],
            ['key' => 'product'],
            ['key' => 'brand'],
        ]);
        Cache::forget('_company_info');
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
