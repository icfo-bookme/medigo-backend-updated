<?php
namespace App\Http\Controllers\Api\Service;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\OrderWithCall;

class ProductService
{
    public function getCategoriesWithProducts()
    {

        $categories = Category::apiQuickSelect()
            ->checkHasProduct()
            ->active()
            ->limit(4)
            ->get();

        $categories->each(function ($category) {
            $category->load(['products' => function ($query) {
                $query->ApiQuickSelect()->ApiQuickRelation()->active()->take(7);
            }]);
        });

        $order_with_call = OrderWithCall::where('status', 1)->first();

        return [
            'categories'      => $categories,
            'order_with_call' => $order_with_call,
        ];
    }

    public function getProductDetails($slug)
    {
        $product = Product::with($this->productBasicRelations())
            ->where('slug', $slug)
            ->first();

        return $product;
    }

    public function similarGenericProducts($slug)
    {
        $genericId = Product::where('slug', $slug)->value('generic_id');

        if (! $genericId) {
            return null;
        }

        return Product::getSimilarProductsByGenericId($genericId);
    }

    public function searchProduct($name)
    {
        $result = Product::where('name', 'LIKE', '%' . $name . '%')
            ->orWhere('slug', 'LIKE', '%' . $name . '%')
            ->select('id', 'name', 'slug', 'category_id', 'generic_id', 'brand_id', 'image')
            ->with($this->productBasicRelations())
            ->orWhereHas('generic', function ($genericQuery) use ($name) {
                $genericQuery->where('generic_name', 'LIKE', '%' . $name . '%');
            })
            ->selectRaw(
                "(CASE
                WHEN name = ? THEN 10                           -- Exact match for name
                WHEN slug = ? THEN 9                            -- Exact match for slug
                WHEN name LIKE ? THEN 8                         -- Prefix match for name
                WHEN slug LIKE ? THEN 7                         -- Prefix match for slug
                WHEN name LIKE ? THEN 6                         -- Contains match for name (non-prefix)
                WHEN slug LIKE ? THEN 5                         -- Contains match for slug (non-prefix)
                WHEN EXISTS(SELECT 1 FROM generics WHERE generics.id = products.generic_id AND generic_name = ?) THEN 4 -- Exact match for generic_name
                WHEN EXISTS(SELECT 1 FROM generics WHERE generics.id = products.generic_id AND generic_name LIKE ?) THEN 3 -- Prefix match for generic_name
                WHEN EXISTS(SELECT 1 FROM generics WHERE generics.id = products.generic_id AND generic_name LIKE ?) THEN 2 -- Contains match for generic_name (non-prefix)
                ELSE 0
            END) AS relevance_score",
                [$name, $name, "$name%", "$name%", "%$name%", "%$name%", $name, "$name%", "%$name%"]
            )
            ->orderByDesc('relevance_score')
            ->orderBy('name', 'asc')
            ->paginate(15);

        if ($result->isEmpty()) {
            $nearestWord = $this->findNearestWord($name);
            $result      = Product::whereIn('name', $nearestWord)
                ->orWhereHas('generic', function ($genericQuery) use ($nearestWord) {
                    $genericQuery->whereIn('generic_name', $nearestWord);
                })
                ->select('id', 'name', 'slug', 'category_id', 'generic_id', 'brand_id', 'image')
                ->with($this->productBasicRelations())
                ->orderBy('name', 'asc')
                ->paginate(15);
        }

        return $result;
    }

    public function findNearestWord($input)
    {
        $combinedResults = DB::table('products')
            ->select('name')
            ->unionAll(DB::table('generics')->select('generic_name as name'))
            ->get()
            ->pluck('name')
            ->toArray();

        $threshold    = 51;
        $similarWords = [];

        foreach ($combinedResults as $result) {
            similar_text(strtolower($input), strtolower($result), $percentage);

            if ($percentage >= $threshold) {
                $similarWords[] = $result;
            }
        }

        usort($similarWords, function ($a, $b) use ($input) {
            $distanceA = levenshtein(strtolower($input), strtolower($a));
            $distanceB = levenshtein(strtolower($input), strtolower($b));
            return $distanceA - $distanceB;
        });

        return $similarWords;
    }

    private function productBasicQuery($query)
    {
        return $query->select('id', 'slug', 'category_id', 'name', 'generic_id', 'brand_id', 'image', 'product_type', 'medical_overview', 'quick_tips', 'brief_description', 'disclaimer', 'indication')
            ->with($this->productBasicRelations());
    }
    private function productQuickQuery($query)
    {
        return $query->select('id', 'slug', 'category_id', 'name', 'generic_id', 'brand_id', 'image', 'status')
            ->with('generic:id,generic_name', 'company:id,name', 'productUnits:id,product_id,product_unit_id,price,discount,qty', 'productUnits.unit:id,unit_name');
    }
    private function productQuickRelations()
    {
        return [
            'productUnits:id,product_id,product_unit_id,price,discount,qty,campaign_id,campaign_price',
        ];
    }

    private function productBasicRelations()
    {
        return [
            'generic:id,generic_name',
            'company:id,name',
            'productUnits:id,product_id,product_unit_id,price,discount,qty,campaign_id,campaign_price',
            'productUnits.unit:id,unit_name',
        ];
    }

    private function productDetailsRelations()
    {
        return [
            'productUnits:id,product_id,product_unit_id,price,discount,qty,campaign_id,campaign_price',
            'productUnits.unit:id,unit_name',
            'category:id,name,slug',
            'company:id,name',
            'generic:id,generic_name',
            'generic.genericDetails:id,generic_id,title,description',
            'generic.similarProducts:id,name,slug,generic_id,brand_id,image',
            'generic.similarProducts.productUnits:id,product_id,product_unit_id,price,discount,qty,campaign_id,campaign_price',
            'generic.similarProducts.productUnits.unit:id,unit_name',
            'generic.similarProducts.generic:id,generic_name',
            'generic.similarProducts.company:id,name',
        ];
    }
}
