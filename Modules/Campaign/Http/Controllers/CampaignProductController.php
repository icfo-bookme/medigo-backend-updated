<?php

namespace Modules\Campaign\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignProduct;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;

class CampaignProductController extends BaseController
{
    public function __construct(CampaignProduct $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('campaign-access')) {
            $this->setPageData('Campaign Product', 'Campaign Product', 'fas fa-gift', [['name' => 'Campaign Product']]);
            return view('campaign::campaign-product.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('campaign-access')) {
            $this->set_datatable_default_properties($request); //set datatable default properties
            $list = $this->model->getDatatableList(); //get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('campaign-delete')) {
                    $action .= ' <a class="dropdown-item delete_data" data-id="' . $value->id . '" data-name="' . $value->campaign->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }

                $discount = ($value->campaign->discount_type == 'percentage')
                ? $value->campaign->discount_amount . ' %'
                : $value->campaign->discount_amount . ' Tk';

                $row = [];
                $row[] = $no;
                $row[] = $value->campaign->name;
                $row[] = '<b>Start Date: </b>' . date('Y-m-d h:i:s A', strtotime($value->campaign->start_date)) . '<br><b>End Date: </b>' . date('Y-m-d h:i:s A', strtotime($value->campaign->end_date));
                $row[] = $discount;
                $row[] = STATUS_LABEL[$value->campaign->status];
                $row[] = action_button($action); //custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function create()
    {
        if (permission('campaign-add')) {
            $this->setPageData('Campaign Product', 'Campaign Product Add', 'fas fa-user-secret', [['name' => 'Campaign Product Add']]);
            $data = [
                'campaigns' => Campaign::getCampaignsByType(1), // 1 for product campaign
                'categories' => Category::allCategories(),
                'products' => [],
            ];
            return view('campaign::campaign-product.create', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function store_or_update(Request $request)
    {
        if ($request->ajax() && permission('campaign-add')) {
            DB::beginTransaction();
            try {
                $campaignId = $request->campaign_id;
                $campaign = Campaign::find($campaignId);

                if (!$campaignId) {
                    return response()->json(['status' => 'error', 'message' => 'Campaign ID is required.']);
                }

                $productIds = collect($request->products)
                    ->filter(fn($item) => isset($item['product_check']) && $item['product_check'] == '1' && is_numeric($item['product_id']))
                    ->pluck('product_id')
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->sort()
                    ->values();

                $existingCampaignProduct = $this->model->where('campaign_id', $campaignId)->first();

                $existingProductIds = collect(optional($existingCampaignProduct)->product_ids ?? [])->map(fn($id) => (int) $id);

                // Calculate removed products
                $removedProductIds = $existingProductIds->diff($productIds);

                $data = [
                    'product_ids' => $productIds->values()->toArray(),
                    'modified_by' => optional(auth()->user())->name,
                    'updated_at' => now(),
                ];

                if ($existingCampaignProduct) {
                    // Update existing campaign product
                    $existingCampaignProduct->update($data);

                    $activityType = 'campaign_product_update';
                    $statusName = 'Updated';
                } else {
                    // Create new campaign product
                    $this->model->create(array_merge($data, [
                        'campaign_id' => $campaignId,
                        'created_by' => optional(auth()->user())->name,
                        'created_at' => now(),
                    ]));

                    $activityType = 'campaign_product_create';
                    $statusName = 'Created';
                }

                foreach ($productIds as $productId) {
                    $product = ProductUnit::where('product_id', $productId)->get();
                    foreach ($product as $item) {
                        $item->update([
                            'campaign_id' => $campaignId,
                            'campaign_price' => $item->price - ($campaign->discount_type == 'percentage'
                                ? ($item->price * $campaign->discount_amount) / 100
                                : $campaign->discount_amount),
                        ]);
                    }
                }

                // Reset campaign details for removed products
                foreach ($removedProductIds as $removedProductId) {
                    $removedProductUnits = ProductUnit::where('product_id', $removedProductId)->where('campaign_id', $campaignId)->get();
                    foreach ($removedProductUnits as $item) {
                        $item->update([
                            'campaign_id' => null,
                            'campaign_price' => 0.00,
                        ]);
                    }
                }

                // Log the activity
                $campaign->user_activity()->updateOrCreate([
                    'activity_type' => $activityType,
                    'status_name' => $statusName,
                    'user_id' => auth()->id(),
                ]);

                DB::commit();
                $output = ['status' => 'success', 'message' => 'Data Saved Successfully'];
            } catch (Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }

            return response()->json($output);
        } else {
            return $this->access_blocked();
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('campaign-delete')) {
            DB::beginTransaction();
            try {
                // Fetch the campaign product row
                $campaignProduct = $this->model->find($request->id);
                if ($campaignProduct) {
                    $campaignId = $campaignProduct->campaign_id;
                    $productIds = $campaignProduct->product_ids;

                    $campaign = Campaign::find($campaignId);

                    // Reset campaign details for removed products
                    foreach ($productIds as $productId) {
                        $productUnits = ProductUnit::where('product_id', $productId)->where('campaign_id', $campaignId)->get();
                        foreach ($productUnits as $item) {
                            $item->update([
                                'campaign_id' => null,
                                'campaign_price' => 0.00,
                            ]);
                        }
                    }

                    // Log the activity
                    $campaign->user_activity()->updateOrCreate([
                        'activity_type' => 'campaign_product_delete',
                        'status_name' => 'Deleted',
                        'user_id' => auth()->id(),
                    ]);

                    $campaignProduct->delete();
                    DB::commit();
                    $output = ['status' => 'success', 'message' => 'Data Deleted Successfully'];
                } else {
                    $output = ['status' => 'error', 'message' => 'Data not found'];
                }
            } catch (Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return $this->access_blocked();
        }
    }

    public function getCampaign(Request $request)
    {
        $campaign_id = $request->id;
        $data = Campaign::where('id', $campaign_id)->first();
        return response()->json($data);
    }

    public function getProduct(Request $request)
    {
        $search_text = $request->search_text;
        $campaign_id = optional($request)->campaign_id;
        $category_id = $request->category_id;

        // Fetch filtered product units
        $products = Product::getProductsWithFilters($search_text, $category_id);
        return view('campaign::campaign-product.product_section', compact('products', 'campaign_id'))->render();
    }

    public function getListedProduct(Request $request)
    {
        $campaign_id = $request->campaign_id;
        $products = Product::getProductsWithFilters(null, null, $campaign_id);
        return view('campaign::campaign-product.listed_product', compact('products', 'campaign_id'))->render();
    }
}
