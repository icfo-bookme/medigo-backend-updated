<?php

namespace Modules\Coupon\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Entities\CouponCategory;

class CategoryCouponController extends BaseController
{
    public function __construct(Coupon $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('manage-coupon')) {
            $this->setPageData('Category Coupon', 'Category Coupon', 'fas fa-balance-scale', [['name' => 'Category Coupon']]);
            $data = [
                'coupons' => Coupon::where('coupon_type', 2)->pluck('name','id'),
                'categories' => Category::pluck('name','id'),
            ];
            return view('coupon::category-coupon.index', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() and permission('manage-coupon')) {
            if (!empty($request->check_category)) {
                $this->model->checkCategory($request->check_category);
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('coupon-edit')) {
                    $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
//                if (permission('coupon-delete')) {
//                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
//                }

                $cat = '';
                if ($value->categories->count() > 0) {
                    $categories = $value->categories->toArray();
                    $categoryChunks = array_chunk($categories, 3);
                    foreach ($categoryChunks as $chunk) {
                        $cat .= '<div class="test-width text-nowrap">';
                        foreach ($chunk as $item) {
                            $cat .= '<span class="label label-success label-pill label-inline rounded mr-1" style="min-width:70px !important;">' . $item['category']['name'] . '</span>';
                            $cat .= ' ';
                        }
                        $cat .= '</div><br>';
                    }
                }

                $row = [];
                $row[] = $no;
                $row[] = $value->name;
                $row[] = $cat;
                $row[] = '<b>Start Date: </b>' . date('Y-m-d h:i:s A', strtotime($value->start_date)) . '<br><b>End Date: </b>' . date('Y-m-d h:i:s A', strtotime($value->end_date));
                $row[] = permission('coupon-edit') ? change_status($value->id, $value->categories[0]->status, $value->name) : STATUS_LABEL[$value->categories[0]->status];
                $row[] = action_button($action); //custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store(Request $request)
    {
        if ($request->ajax() and permission('coupon-add')) {
            $validator = Validator::make($request->all(), [
                'coupon_id' => 'required|integer',
                'type' => 'required|in:1,2',
                'value' => 'required|numeric',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'category_id' => 'required|array',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
            }
            DB::beginTransaction();
            try {
                $couponId = $request->coupon_id;
                $data = [];
                if ($request->category_id) {
                    foreach ($request->category_id as $categoryId) {
                        $data[] = [
                            'coupon_id' => $couponId,
                            'category_id' => $categoryId,
                            'created_at' => now(),
                        ];
                    }
                    CouponCategory::insert($data);
                    DB::commit();
                    $output = ['status' => 'success', 'message' => 'Data Saved Successfully'];
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->access_blocked());
        }
    }

    public function update(Request $request)
    {
        if ($request->ajax() and permission('coupon-edit')) {
            $validator = Validator::make($request->all(), [
                'coupon_id' => 'required|integer',
                'type' => 'required|in:1,2',
                'value' => 'required|numeric',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'category_id' => 'required|array',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
            }
            DB::beginTransaction();
            try {
//                $coupon = Coupon::findOrFail($request->coupon_id);
//                $coupon->syncCategories()->sync($request->category_id);
                $couponId = $request->coupon_id;
                $categoryIds = collect($request->category_id);
                CouponCategory::where('coupon_id', $couponId)->whereNotIn('category_id', $categoryIds)->delete();

                if ($request->category_id) {
                    foreach ($request->category_id as $categoryId) {
                        CouponCategory::updateOrCreate(
                            ['coupon_id' => $couponId, 'category_id' => $categoryId],
                            ['coupon_id' => $couponId, 'category_id' => $categoryId, 'updated_at' => now()]
                        );
                    }
                    DB::commit();
                    $output = ['status' => 'success', 'message' => 'Data Updated Successfully'];
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->access_blocked());
        }
    }

    public function edit(Request $request)
    {
        if ($request->ajax() and permission('coupon-edit')) {
            $data = $this->model->with('categories')->find($request->id);
            $output = $this->data_message($data);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() and permission('coupon-delete')) {
            return 'ss';
            $result = $this->model->find($request->id)->get();
            $output = $this->delete_message($result);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function get_coupon(Request $request)
    {
        if (request()->ajax() and permission('coupon-add')) {
            $coupon = Coupon::find($request->id);
            return response()->json($coupon);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if ($request->ajax() && permission('coupon-edit')) {
            $coupon = $this->model->with('categories')->find($request->id);

            if (!$coupon) {
                return response()->json(['status' => 'error', 'message' => 'Coupon not found']);
            }

            $status = $request->status;

            foreach ($coupon->categories as $category) {
                $category->update(['status' => $status]);
            }

            $output = ['status' => 'success', 'message' => 'Status has been changed successfully'];
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
