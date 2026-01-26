<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Category;
use App\Traits\CacheHelperTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class CategoriesController extends BaseController
{
    use CacheHelperTrait; // Use the CacheHelperTrait for caching methods

    public function __construct()
    {
        $this->cache_checker(); // Initialize cache checker
    }

    public function get_category()
    {
        $cacheKey = 'all_categories';

        // Reuse getCachedData method from CacheHelperTrait
        $data = $this->getCachedData($cacheKey, function () {
            return Category::allCategories(); // Fetch all categories from the database
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], Response::HTTP_OK);
    }

    public function get_category_wise_product($slug, Request $request)
    {
        // Get the current page and the number of items per page (default to 8)
        $perPage = $request->input('per_page', 8); // Number of items per page
        $page = $request->input('page', 1); // Current page, default to page 1

        // Generate a unique cache key based on category slug, page, and per_page for products
        $cacheKeyProducts = 'category_products_' . $slug . '_page_' . $page . '_per_page_' . $perPage;
        $cacheKeyCategory = 'category_' . $slug; // Cache key for category details

        // Use getCachedData method to fetch both category and product data
        $category = $this->getCachedData($cacheKeyCategory, function () use ($slug) {
            return Category::where('slug', $slug)->firstOrFail(); // Fetch category data
        });

        $products = $this->getCachedData($cacheKeyProducts, function () use ($category, $perPage) {
            return $category->products()
                ->ApiQuickSelect()
                ->active()
                ->orderBy('id', 'desc')
                ->paginate($perPage); // Paginate products based on perPage
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'id'       => $category->id,
                'slug'     => $category->slug,
                'name'     => $category->name,
                'products' => $products,
            ],
        ]);
    }
}
