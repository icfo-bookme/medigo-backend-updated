<?php

namespace Modules\Frontend\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Slider extends Model
{
    protected $table = 'sliders';

    protected $fillable = ['url', 'image'];

      public static function flushCurrentCache()
    {

        DB::table('ecom_caches')->insert([
            ['key' => 'category'],
            ['key' => 'product'],
            ['key' => 'brand'],
        ]);

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
