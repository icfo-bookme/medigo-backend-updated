<?php

namespace Modules\Campaign\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Http\Requests\CampaignRequest;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;

class CampaignController extends BaseController
{
    use UploadAble;

    public function __construct(Campaign $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('campaign-access')) {
            $this->setPageData('Campaigns', 'Campaigns', 'fas fa-gift', [['name' => 'Campaigns']]);
            return view('campaign::manage-campaign.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('campaign-access')) {

            $fields = [
                'name' => 'setName',
                'status' => 'setStatus',
                'sort_table' => 'setTableOrder'
            ];

            foreach ($fields as $field => $method) {
                if (!empty($request->$field)) {
                    $this->model->$method($request->$field);
                }
            }


            $this->set_datatable_default_properties($request); //set datatable default properties
            $list = $this->model->getDatatableList(); //get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('campaign-edit')) {
                    $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
                if (permission('campaign-delete')) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }

                // Initialize $discountType with a default value
                $discountType = '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Unknown</span>';
                if ($value->discount_type == 'percentage') {
                    $discountType = '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Percentage</span>';
                }
                if ($value->discount_type == 'amount') {
                    $discountType = '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Flat Amount</span>';
                }

                $row = [];
                $row[] = $no;
                $row[] = '<b>Name: </b>' . $value->name .' <br> '. CAMPAIGN_TYPE_LABEL[$value->campaign_type] ;
                $row[] = $this->table_image(CAMPAIGN_IMAGE_PATH, $value->image, $value->name);
                $row[] = '<b>Start Date: </b>' . date('Y-m-d h:i:s A', strtotime($value->start_date)) . '<br><b>End Date: </b>' . date('Y-m-d h:i:s A', strtotime($value->end_date));
                $row[] = '<div class="text-left"><br><b>Type: </b>' . $discountType . '<br><b>Amount: </b>' . $value->discount_amount . '</div>';
                $row[] = permission('campaign-edit') ? change_status($value->id, $value->status, $value->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action); //custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(CampaignRequest $request)
    {
        if ($request->ajax() && (permission('campaign-add') || permission('campaign-edit'))) {
            $collection = collect($request->all())->except('image');
            $image = $request->old_image;

            if ($request->has('image')) {
                $image = $this->upload_file($request->file('image'), CAMPAIGN_IMAGE_PATH, null, 'public');
                if (!empty($request->old_image)) {
                    $this->delete_file($request->old_image, CAMPAIGN_IMAGE_PATH);
                }
            }

            $collection = $collection->merge(['image' => $image]);
            $collection = $this->track_data($collection, $request->update_id);
            $campaign = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());

            // Update related products table data
            if ($campaign) {
                ProductUnit::where('campaign_id', $campaign->id)->get()->each(function ($data) use ($campaign) {
                    if ($campaign->campaign_type == 1) {
                        $data->update([
                            'campaign_product_price' => $data->price - ($campaign->discount_type == 1
                                    ? ($data->price * $campaign->discount_amount) / 100
                                    : $campaign->discount_amount),
                            'campaign_category_price' => 0.00
                        ]);
                    } else {
                        $data->update([
                            'campaign_category_price' => $data->price - ($campaign->discount_type == 1
                                    ? ($data->price * $campaign->discount_amount) / 100
                                    : $campaign->discount_amount),
                            'campaign_product_price' => 0.00
                        ]);
                    }
                });
            }

            // User Activity
            $campaign->user_activity()->updateOrCreate([
                'activity_type' => $request->update_id ? 'campaign_updated' : 'campaign_created',
                'status_name' => $request->update_id ? 'Updated' : 'Created',
                'user_id' => auth()->id(),
            ]);

            $output = $this->store_message($campaign, $request->update_id);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if ($request->ajax() && permission('campaign-edit')) {
            $data = $this->model->findOrFail($request->id);
            $output = $this->data_message($data);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if ($request->ajax() && permission('campaign-edit')) {
            $result = $this->model->find($request->id)->update(['status' => $request->status]);
            $output = $result ? ['status' => 'success', 'message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error', 'message' => 'Failed To Change Status'];
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
