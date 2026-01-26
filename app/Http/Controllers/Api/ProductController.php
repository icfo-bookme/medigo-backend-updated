<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Service\ProductService;
use App\Http\Controllers\BaseController;
use App\Traits\CacheHelperTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class ProductController extends BaseController
{
    use CacheHelperTrait; // Use the cache helper trait

    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        $this->cache_checker(); // Call cache checker on initialization
    }

    public function get_product(Request $request)
    {
        // Define a unique cache key based on the request parameters
        $cacheKey = 'categories_with_products';

        // Use the helper method for caching
        $data = $this->getCachedData($cacheKey, function () {
            return $this->productService->getCategoriesWithProducts(); // Get data from service
        });

        return response()->json([
            'success' => true,
            'data' => $data['categories'],
            'order_with_call' => $data['order_with_call'],
        ]);
    }

    public function get_product_details($slug)
    {
        // Define a unique cache key based on product slug
        $cacheKey = 'product_details_' . $slug;

        // Use the helper method for caching
        $data = $this->getCachedData($cacheKey, function () use ($slug) {
            return $this->productService->getProductDetails($slug); // Get data from service
        });

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'No similar products found for the given generic.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], Response::HTTP_OK);
    }

    public function getSimilarGenericProducts($slug)
    {
        // Define a unique cache key for similar products
        $cacheKey = 'similar_generic_products_' . $slug;

        // Use the helper method for caching
        $data = $this->getCachedData($cacheKey, function () use ($slug) {
            return $this->productService->similarGenericProducts($slug); // Get data from service
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function search_product($name)
    {
        // Define a unique cache key for product search
        $cacheKey = 'search_product_' . $name;

        // Use the helper method for caching
        $result = $this->getCachedData($cacheKey, function () use ($name) {
            return $this->productService->searchProduct($name); // Get data from service
        });

        return response()->json($result);
    }
}
