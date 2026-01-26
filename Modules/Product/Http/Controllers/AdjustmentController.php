<?php

namespace Modules\Product\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\Adjustment;
use Modules\Product\Entities\ProductUnit;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Entities\AdjustmentProduct;
use Modules\Product\Entities\WarehouseProduct;
use Modules\Product\Http\Requests\AdjustmentFormRequest;
use Modules\Setting\Entities\Warehouse;

class AdjustmentController extends BaseController
{

    public function __construct(Adjustment $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('adjustment-access')) {
            $this->setPageData('Manage Adjustment', 'Manage Adjustment', 'fas fa-shopping-cart', [['name' => 'Manage Adjustment']]);
            $data = [
                'warehouses' => Warehouse::allWarehouses()
            ];
            return view('product::adjustment.index', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('adjustment-access')) {
            $fields = [
                'adjustment_no' => 'setAdjustmentNo',
                'from_date' => 'setFromDate',
                'to_date' => 'setToDate',
                'warehouse_id' => 'setWarehouseID',
            ];

            foreach ($fields as $field => $method) {
                if (!empty($request->$field)) {
                    $this->model->$method($request->$field);
                }
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('adjustment-access')) {
                    $action .= ' <a class="dropdown-item" href="' . route("adjustment.edit", $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }

                if (permission('adjustment-view')) {
                    $action .= ' <a class="dropdown-item view_data" href="' . route("adjustment.view", $value->id) . '">' . self::ACTION_BUTTON['View'] . '</a>';
                    $action .= ' <a class="dropdown-item view_log" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Log'] . '</a>';
                }

                if (permission('adjustment-delete')) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->adjustment_no . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }

                $row = [];
                if (permission('adjustment-bulk-delete')) {
                    $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                }
                $row[] = $no;
                $row[] = $value->adjustment_no;
                if (empty(auth()->user()->warehouse_id)) {
                    $row[] = $value->warehouse->name;
                }
                $row[] = $value->item;
                $row[] = number_format($value->total_qty, 2);
                $row[] = date(config('settings.date_format'), strtotime($value->created_at));
                $row[] = action_button($action);//custom helper function for action button
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
        if (permission('adjustment-add')) {
            $this->setPageData('Add Adjustment', 'Add Adjustment', 'fas fa-adjust', [['name' => 'Add Adjustment']]);
            $data = [
                'adjustment_no' => 'ADJ-' . date('YmdH') . rand(1, 999),
                'warehouses' => Warehouse::allWarehouses()
            ];
            return view('product::adjustment.create', $data);
        } else {
            return $this->access_blocked();
        }

    }

    public function store(AdjustmentFormRequest $request)
    {
        if ($request->ajax() && permission('adjustment-add')) {
            DB::beginTransaction();
            try {
                $adjustment = $this->model->create([
                    'warehouse_id' => $request->warehouse_id,
                    'adjustment_no' => $request->adjustment_no,
                    'item' => $request->item,
                    'total_qty' => $request->total_qty,
                    'note' => $request->note,
                    'created_by' => auth()->user()->name
                ]);

                //Adjustment User Activity
                $adjustment->user_activity()->create([
                    'activity_type' => 'adjustment_create',
                    'status_name' => 'Created',
                    'user_id' => auth()->id(),
                ]);

                //purchase products
                $products = [];
                if ($request->has('products')) {
                    foreach ($request->products as $value) {
                        $value = (object) $value;
                        $products[] = [
                            'adjustment_id' => $adjustment->id,
                            'product_id' => $value->id,
                            'qty' => $value->qty,
                            'action' => $value->action,
                            'created_at' => now(),
                        ];

                        $product = ProductUnit::where('product_id', $value->id)->first();
                        if ($product) {
                            $value->action == '+' ? $product->qty += $value->qty : $product->qty -= $value->qty;
                            $product->update();
                        }
                    }
                    if (count($products) > 0) {
                        AdjustmentProduct::insert($products);
                    }
                }
                DB::commit();
                $output = $this->store_message($adjustment, null);
            } catch (Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function show(int $id)
    {
        if (permission('adjustment-view')) {
            $this->setPageData('Adjustment Details', 'Adjustment Details', 'fas fa-file', [['name' => 'Adjustment', 'link' => route('adjustment')], ['name' => 'Adjustment Details']]);
            $adjustment = $this->model->with('products', 'warehouse')->find($id);
            return view('product::adjustment.details', compact('adjustment'));
        } else {
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if (permission('adjustment-access')) {
            $this->setPageData('Edit Adjustment', 'Edit Adjustment', 'fas fa-edit', [['name' => 'Adjustment', 'link' => route('adjustment')], ['name' => 'Edit Adjustment']]);
            $data = [
                'adjustment' => $this->model->with('products')->find($id),
                'warehouses' => Warehouse::allWarehouses()
            ];
            return view('product::adjustment.edit', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function update(AdjustmentFormRequest $request)
    {
        if ($request->ajax() && permission('adjustment-access')) {
            DB::beginTransaction();
            try {
                $adjustment = $this->model->findOrFail($request->adjustment_id);
                $adjustment->update([
                    'warehouse_id' => $request->warehouse_id,
                    'adjustment_no' => $request->adjustment_no,
                    'item' => $request->item,
                    'total_qty' => $request->total_qty,
                    'note' => $request->note,
                    'modified_by' => auth()->user()->name
                ]);

                // Update Adjustment User Activity
                $adjustment->user_activity()->create([
                    'activity_type' => 'adjustment_update',
                    'status_name' => 'Updated',
                    'user_id' => auth()->id(),
                ]);

                // Update products
                $products = [];
                if ($request->has('products')) {
                    foreach ($request->products as $value) {
                        $value = (object) $value;

                        // Find existing adjustment product or create a new one
                        $adjustmentProduct = AdjustmentProduct::firstOrNew([
                            'adjustment_id' => $adjustment->id,
                            'product_id' => $value->id
                        ]);

                        // Adjust the product quantity accordingly
                        $productUnit = ProductUnit::where('product_id', $value->id)->first();
                        if ($productUnit) {
                            $oldQty = $adjustmentProduct->exists ? $adjustmentProduct->qty : 0;
                            if ($adjustmentProduct->exists) {
                                $adjustmentProduct->action == '+' ? $productUnit->qty -= $oldQty : $productUnit->qty += $oldQty;
                            }

                            $value->action == '+' ? $productUnit->qty += $value->qty : $productUnit->qty -= $value->qty;
                            $productUnit->update();
                        }

                        // Update the adjustment product
                        $adjustmentProduct->qty = $value->qty;
                        $adjustmentProduct->action = $value->action;
                        $adjustmentProduct->save();
                    }
                }

                DB::commit();
                $output = $this->store_message($adjustment, null);
            } catch (Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('adjustment-access')) {
            DB::beginTransaction();
            try {
                $adjustmentData = $this->model->with('products')->find($request->id);
                if (!$adjustmentData->products->isEmpty()) {
                    foreach ($adjustmentData->products as $adjustment_product) {
                        $old_qty = $adjustment_product->pivot->qty;
                        $product_data = Product::find($adjustment_product->id);
                        if ($product_data) {
                            ($adjustment_product->pivot->action == '+') ? $product_data->qty -= $old_qty : $product_data->qty += $old_qty;
                            $product_data->update();
                        }
                        $warehouse_product = WarehouseProduct::where([
                            'warehouse_id' => $adjustmentData->warehouse_id,
                            'product_id' => $adjustment_product->id,
                        ])->first();
                        if ($warehouse_product) {
                            ($adjustment_product->pivot->action == '+') ? $warehouse_product->qty -= $old_qty : $warehouse_product->qty += $old_qty;
                            $warehouse_product->update();
                        }
                    }
                    $adjustmentData->products()->detach();
                }
                $adjustment = $adjustmentData->delete();
                $output = $adjustment ? ['status' => 'success', 'message' => 'Data has been deleted successfully'] : ['status' => 'error', 'message' => 'failed to delete data'];
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->access_blocked());
        }
    }

    public function bulk_delete(Request $request)
    {
        if ($request->ajax() && permission('adjustment-bulk-delete')) {
            DB::beginTransaction();
            try {
                foreach ($request->ids as $id) {
                    $adjustmentData = $this->model->with('products')->find($id);
                    if (!$adjustmentData->products->isEmpty()) {
                        foreach ($adjustmentData->products as $adjustment_product) {
                            $old_qty = $adjustment_product->pivot->qty;
                            $product_data = Product::find($adjustment_product->id);
                            if ($product_data) {
                                ($adjustment_product->pivot->action == '+') ? $product_data->qty -= $old_qty : $product_data->qty += $old_qty;
                                $product_data->update();
                            }
                            $warehouse_product = WarehouseProduct::where([
                                'warehouse_id' => $adjustmentData->warehouse_id,
                                'product_id' => $adjustment_product->id,
                            ])->first();
                            if ($warehouse_product) {
                                ($adjustment_product->pivot->action == '+') ? $warehouse_product->qty -= $old_qty : $warehouse_product->qty += $old_qty;
                                $warehouse_product->update();
                            }
                        }
                        $adjustmentData->products()->detach();
                    }
                }
                $adjustment = $this->model->destroy($request->ids);
                $output = $adjustment ? ['status' => 'success', 'message' => 'Data has been deleted successfully'] : ['status' => 'error', 'message' => 'failed to delete data'];
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->access_blocked());
        }
    }

    public function adjustmentLog(Request $request)
    {
        if ($request->ajax() && permission('sale-view')) {
            $sale = $this->model->with('user_activity')->find($request->id);
            return view('sale::log-data', compact('sale'))->render();
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
