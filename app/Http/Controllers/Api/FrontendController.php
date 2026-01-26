<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\CacheHelperTrait;
use Illuminate\Support\Facades\Cache;
use Modules\Frontend\Entities\About;
use Modules\Frontend\Entities\Privacy;
use Modules\Frontend\Entities\PromotionVideo;
use Modules\Frontend\Entities\Refund;
use Modules\Frontend\Entities\Slider;
use Modules\Frontend\Entities\Term;
use Modules\Setting\Entities\SearchText;
use Symfony\Component\HttpFoundation\Response;

class FrontendController extends Controller
{
    use CacheHelperTrait; // Use the cache helper trait

    public function __construct()
    {
        $this->cache_checker(); // Cache check initialization
    }

    // Fetch Search Text with cache control
    public function getSearchText()
    {
        $cacheKey = 'search_text';

        // Use getCachedData from CacheHelperTrait
        $searchText = $this->getCachedData($cacheKey, function () {
            return SearchText::select('id', 'search_text')->first();
        });

        return response()->json([
            'success' => true,
            'data'    => $searchText,
        ], Response::HTTP_OK);
    }

    // Fetch Company Info with cache control
    public function getCompanyInfo()
    {
        $cacheKey = 'company_info';

        // Use getCachedData from CacheHelperTrait
        $companyInfo = $this->getCachedData($cacheKey, function () {
            return [
                'company_info' => Setting::all(),
                'logo'         => Setting::where('name', 'logo')->first(),
                'about_us'     => About::first(),
                'terms'        => Term::first(),
                'privacy'      => Privacy::first(),
                'refund'       => Refund::first(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $companyInfo,
        ], Response::HTTP_OK);
    }

    // Fetch Promotion Video with cache control
    public function getPromotionVideo()
    {
        $cacheKey = 'promotion_video';

        // Use getCachedData from CacheHelperTrait
        $promotionVideo = $this->getCachedData($cacheKey, function () {
            $data = PromotionVideo::allPromotionVideo();
            return $data->map(function ($video) {
                return [
                    'title' => $video->title,
                    'url'   => $video->url,
                ];
            });
        });

        return response()->json([
            'success' => true,
            'data'    => $promotionVideo,
        ], Response::HTTP_OK);
    }

    // Fetch About Us with cache control
    public function getAboutUs()
    {
        $cacheKey = 'about_us';

        // Use getCachedData from CacheHelperTrait
        $aboutus = $this->getCachedData($cacheKey, function () {
            return About::first();
        });

        return response()->json([
            'success' => $aboutus->isNotEmpty(),
            'data'    => $aboutus,
        ]);
    }

    // Fetch Terms and Conditions with cache control
    public function getTermsAndCondition()
    {
        $cacheKey = 'terms_and_condition';

        // Use getCachedData from CacheHelperTrait
        $terms = $this->getCachedData($cacheKey, function () {
            return Term::first();
        });

        return response()->json([
            'success' => $terms->isNotEmpty(),
            'data'    => $terms,
        ]);
    }

    // Fetch Privacy Policy with cache control
    public function getPrivacyPolicy()
    {
        $cacheKey = 'privacy_policy';

        // Use getCachedData from CacheHelperTrait
        $privacy = $this->getCachedData($cacheKey, function () {
            return Privacy::first();
        });

        return response()->json([
            'success' => $privacy->isNotEmpty(),
            'data'    => $privacy,
        ]);
    }

    // Fetch Return and Refund Policy with cache control
    public function getReturnAndRefundPolicy()
    {
        $cacheKey = 'return_refund_policy';

        // Use getCachedData from CacheHelperTrait
        $refund = $this->getCachedData($cacheKey, function () {
            return Refund::first();
        });

        return response()->json([
            'success' => $refund->isNotEmpty(),
            'data'    => $refund,
        ]);
    }

    // Fetch Sliders with cache control
    public function getSlider()
    {
        $cacheKey = 'sliders';

        // Use getCachedData from CacheHelperTrait
        $slider = $this->getCachedData($cacheKey, function () {
            return Slider::select('url', 'image')->get();
        });

        return response()->json([
            'success' => $slider->isNotEmpty(),
            'data'    => $slider->isNotEmpty() ? $slider : [],
        ]);
    }
}
