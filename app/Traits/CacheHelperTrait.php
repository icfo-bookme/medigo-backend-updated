<?php
namespace App\Traits;

use App\Jobs\CacheCleanerJob;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait CacheHelperTrait
{
    public $ttl           = 60 * 60 * 24 * 3; // 3 days in seconds
    public $is_production =  false;
    protected $productService;

    // Check if cache needs to be reset
    public function cache_checker()
    {
        $has_any_change = DB::table('ecom_caches')->value('key');

        $this->is_production  =  App::environment('production');

        info($this->is_production);

        if ($has_any_change) {
            $this->cache_checker_reset();
        }
    }

    // Reset the cache using the CacheCleanerJob
    public function cache_checker_reset()
    {
        dispatch_sync(new CacheCleanerJob());
    }

    // Method to handle caching
    public function getCachedData($cacheKey, $callback)
    {
        if (! Cache::has($cacheKey) || ! $this->is_production) {
            $data = $callback();                      // Fetch data using the callback
            info('from db');
            Cache::put($cacheKey, $data, $this->ttl); // Cache the data for 3 days
        } else {
            info('from cache');

            $data = Cache::get($cacheKey); // Retrieve from cache if available
        }

        return $data;
    }
}
