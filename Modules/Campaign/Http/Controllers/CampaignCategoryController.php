<?php

namespace Modules\Campaign\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Jobs\callArtisanToRun;
use App\Jobs\RemoveCampaignPricesJob;
use App\Jobs\UpdateCampaignPricesJob;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignCategory;
use Exception;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;


class CampaignCategoryController extends BaseController
{
    public function __construct(CampaignCategory $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('campaign-access')) {
            $this->setPageData('Campaign Category', 'Campaign Category', 'fas fa-gift', [['name' => 'Campaign Category']]);
            return view('campaign::campaign-category.index');
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
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->campaign_id . '" data-name="' . $value->campaign->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
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
            $this->setPageData('Campaign Category', 'Campaign Category Add', 'fas fa-user-secret', [['name' => 'Campaign Category Add']]);
            $data = [
                'campaigns' => Campaign::getCampaignsByType(2), // 2 for category campaign
                'categories' => Category::allCategories(),
                'products' => [],
            ];
            return view('campaign::campaign-category.create', $data);
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

                // Extract and filter the category IDs using collections
                $categoryIds = collect($request->categories)
                    ->filter(function ($item) {
                        return isset($item['category_check']) && $item['category_check'] == '1' && is_numeric($item['category_id']);
                    })
                    ->pluck('category_id')
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->sort()
                    ->values();  
                
                // Fetch the existing campaign category
                $existingCampaignCategory = $this->model->where('campaign_id', $campaignId)->first();
                
                $existingCategoryIds = collect($existingCampaignCategory ? $existingCampaignCategory->category_ids : [])
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->sort()
                    ->values();
                
                // Calculate removed category IDs
                $removedCategoryIds = $existingCategoryIds->diff($categoryIds);
                
                // Log the information
                info('New Category IDs: ', $categoryIds->all());
                info('Existing Category IDs: ', $existingCategoryIds->all());
                info('Removed Category IDs: ', $removedCategoryIds->all());
                
                // Update the existing campaign category if it exists
                $data = [
                    'category_ids' => $categoryIds->jsonSerialize(),
                    'modified_by' => optional(auth()->user())->name,
                    'updated_at' => now(),
                ];
                
                $record = $this->model->updateOrCreate(
                    ['campaign_id' => $campaignId],
                    array_merge($data, [
                        'created_by' => optional(auth()->user())->name,
                        'created_at' => now(),
                    ])
                );
                
                $activityType = $record->wasRecentlyCreated ? 'campaign_category_create' : 'campaign_category_update';
                $statusName = $record->wasRecentlyCreated ? 'Created' : 'Updated';

                // Artisan::call('queue:work');

                try {

                    $customBatchId = 'batch-' . rand(1, 1000) . '-' . now()->format('ymdHis');

                // update the product list for frontend use 
                $batch = Bus::batch([
                    new UpdateCampaignPricesJob($categoryIds->all(), $campaignId, $campaign),
                    new RemoveCampaignPricesJob($removedCategoryIds->all(), $campaignId, $campaign),
                ])->then(function () {
                    // All jobs completed successfully

                    info('All jobs in the batch have been processed.');
                })->catch(function (\Throwable $e) {
                    // Handle any failure in the batch
                    info('A job in the batch failed: ' . $e->getMessage());
                })->finally(function () {

                    // Always executed regardless of success or failure
                    info('Batch processing finished.');

                })
                ->name('Update Campaign Prices - ' . $customBatchId)
                ->allowFailures()
                ->dispatch();

                // callArtisanToRun::dispatchSync();

                
                // Log the custom batch ID if needed
                info('Custom batch dispatched with ID: ' . $customBatchId);


               } catch (\Exception $e) {
                info("Job failed: " . $e->getMessage());

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

    public function getCategory(Request $request)
    {
        $search_text = $request->search_text;
        $campaign_id = optional($request)->campaign_id;
        $category_id = $request->category_id;

        // Fetch filtered categories
        $categories = Category::getCategoriesWithFilters($search_text, $category_id);
        return view('campaign::campaign-category.category_section', compact('categories', 'campaign_id'))->render();
    }

    public function getListedCategory(Request $request)
    {
        $campaign_id = $request->campaign_id;
        $campaignCategory = $this->model->where('campaign_id', $campaign_id)->first();
        $categoryIds = $campaignCategory ? $campaignCategory->category_ids : [];

        if (empty($categoryIds) || !is_array($categoryIds)) {
            return response()->json(['status' => 'error', 'message' => 'No categories found for this campaign.']);
        }

        $categories = Category::whereIn('id', $categoryIds)->get();
        return view('campaign::campaign-category.listed_category', compact('categories', 'campaign_id'))->render();
    }
}
