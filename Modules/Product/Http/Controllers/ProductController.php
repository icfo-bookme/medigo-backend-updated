<?php

namespace Modules\Product\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Keygen\Keygen;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Category;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Modules\Product\Entities\Generic;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;
use Modules\Sale\Entities\OrderPackage;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleProduct;
use Modules\Product\Entities\Attribute;
use App\Http\Controllers\BaseController;
use Modules\Product\Http\Requests\ProductFormRequest;

class ProductController extends BaseController
{
    use UploadAble;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('product-access')) {
            $this->setPageData('Product Manage', 'Product Manage', 'fab fa-product-hunt', [['name' => 'Product Manage']]);
            $data = [
                'units' => Unit::where('status', 1)->pluck('unit_name', 'id'),
                'categories' => Category::pluck('name', 'id'),
                'brands' => Brand::pluck('name', 'id'),
            ];
            return view('product::index', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('product-access')) {
            $fields = [
                'name' => 'setName',
                'generic_name' => 'setGenericName',
                'brand_id' => 'setBrandID',
                'category_id' => 'setCategoryID',
                'status' => 'setStatus',
                'sort_table' => 'setTableOrder',
            ];

            foreach ($fields as $field => $method) {
                if (!empty($request->$field)) {
                    $this->model->$method($request->$field);
                }
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('product-edit')) {
                    $action .= ' <a class="dropdown-item" href="' . route("product.edit", $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
                if (permission('product-view')) {
                    $action .= ' <a class="dropdown-item" href="' . url("product/view/" . $value->id) . '">' . self::ACTION_BUTTON['View'] . '</a>';
                    $action .= ' <a class="dropdown-item view_log" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Log'] . '</a>';
                }

                $row = [];
                $row[] = row_checkbox($value->id);
                $row[] = $value->id;
                $row[] = '<a href="#" class="show-image" data-id="' . $value->id . '"  data-toggle="modal" data-target="#exampleModalLong"  data-image_path="' . asset("storage/" . PRODUCT_IMAGE_PATH) . '" data-toggle="modal" data-target="#largeModal">' . $this->table_image(PRODUCT_IMAGE_PATH, $value->image, $value->name) . ' </a>';
                $row[] = PRODUCT_TYPES[$value->product_type];
                $row[] = $value->name;
                $row[] = $value->generic->generic_name;
                $row[] = $value->brand->name;
                $row[] = $value->category->name;
                $row[] = permission('product-edit') ? change_status($value->id, $value->status, $value->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(), $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function create()
    {
        if (permission('product-add')) {
            $this->setPageData('Add Product', 'Add Product', 'fab fa-product-hunt', [['name' => 'Product', 'link' => route('product')], ['name' => 'Add Product']]);
            $data = [
                'categories' => Category::pluck('name', 'id'),
                'units' => Unit::pluck('unit_name', 'id'),
                'taxes' => Tax::activeTaxes(),
                'brands' => Brand::pluck('name', 'id'),
                'generic' => Generic::pluck('generic_name', 'id')
            ];

            return view('product::create', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function store(ProductFormRequest $request)
    {
        if ($request->ajax() && permission('product-add')) {
            DB::beginTransaction();
            try {
                $collection = collect($request->all());
                $collection = $this->track_data($collection, $request->update_id);
                $image = $request->old_image;
                if ($request->hasFile('image')) {
                    $image = $this->upload_file($request->file('image'), PRODUCT_IMAGE_PATH);
                    if (!empty($request->old_image)) {
                        $this->delete_file($request->old_image, PRODUCT_IMAGE_PATH);
                    }
                }
                $slugs = Str::slug($request->name . Str::random(10), '-');
                $collection = $collection->merge(compact('image'));
                $result = $this->model->updateOrCreate(['id' => $request->update_id, 'slug' => $slugs], $collection->all());

                $similar_products = [];
                if ($request->has('similar_product_id')) {
                    foreach ($request->similar_product_id as $key => $product) {
                        $similar_products[] = [
                            'similar_product_id' => $product
                        ];
                    }
                }
                $result->similar_products()->sync($similar_products);

                foreach ($collection['price'] as $key => $val) {
                    $p_unit = new ProductUnit();
                    $p_unit->product_id = $result->id;
                    $p_unit->product_unit_id = $collection['product_unit_id'][$key];
                    $p_unit->item_code = $collection['item_code'][$key] ?? 0;
                    $p_unit->price = $collection['price'][$key] ?? 0;
                    $p_unit->discount = $collection['discount'][$key] ?? 0;
                    $p_unit->qty = 0;
                    $p_unit->alert_qty = $collection['alert_qty'][$key] ?? 0;
                    $p_unit->save();
                }
                $output = $this->store_message($result, null);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $th->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit(int $id)
    {
        if (permission('product-edit')) {
            $this->setPageData('Edit Product', 'Edit Product', 'fas fa-edit', [['name' => 'Product', 'link' => route('product')], ['name' => 'Edit Product']]);
            $data = [
                'categories' => Category::pluck('name', 'id'),
                'units' => Unit::pluck('unit_name', 'id'),
                'taxes' => Tax::activeTaxes(),
                'brands' => Brand::pluck('name', 'id'),
                'generic' => Generic::pluck('generic_name', 'id'),
                'product' => Product::find($id),
//                'products' => Product::with('similar_product_list')->latest()->get()
            ];
            return view('product::edit', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function update(ProductFormRequest $request)
    {
        if ($request->ajax() && permission('product-add')) {
            DB::beginTransaction();
            try {
                $collection = collect($request->all())->merge(['product_type' => $request->product_type ?? 1]);
                $collection = $this->track_data($collection, $request->update_id);
                $image = $request->old_image;
                if ($request->hasFile('image')) {
                    $file = $request->file('image');

                    $image = $this->upload_file($request->file('image'), PRODUCT_IMAGE_PATH);
                    if (!empty($request->old_image)) {
                        $this->delete_file($request->old_image, PRODUCT_IMAGE_PATH);
                    }
                }

                $collection = $collection->merge(compact('image'));

                $result = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
                // Log product update activity
                $result->user_activity()->create([
                    'activity_type' => 'product_update',
                    'status_name' => 'Updated',
                    'user_id' => auth()->id(),
                ]);

                $similar_products = [];
                if ($request->has('similar_product_id')) {
                    foreach ($request->similar_product_id as $key => $product) {
                        $similar_products[] = [
                            'similar_product_id' => $product
                        ];
                    }
                }
                $result->similar_products()->sync($similar_products);

                DB::commit();
                $output = $this->store_message($result, $request->update_id);
            } catch (\Throwable $th) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $th->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function update_bulk_category(Request $request)
    {
        if ($request->ajax() && permission('product-edit')) {
            DB::beginTransaction();
            try {
                $productIds = explode(',', $request->selected_ids);
                $categoryId = $request->category_id;

                Product::whereIn('id', $productIds)->update(['category_id' => $categoryId]);

                DB::commit();
                $output = ['status' => 'success', 'message' => 'Category updated successfully for selected products.'];
            } catch (Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function update_unit_data(Request $request)
    {
        if (permission('product-edit')) {

            DB::beginTransaction();
            try {
                if ($request->update_id) {
                    $collection = collect($request->all())->except('_token', 'unit_name', 'update_id');
                    $checklists = ProductUnit::where('id', $request->update_id);
                    $checklists->update($collection->all());
                    $output = $this->store_message($checklists);
                } else {
                    $collection = collect($request->all())->except('unit_name');
                    $collection = $this->track_data($collection, $request->update_id);
                    $result = ProductUnit::updateOrCreate(['id' => $request->update_id], $collection->all());
                    $output = $this->store_message($result, $request->update_id);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $th->getMessage()];
            }
            return redirect()->back();
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function show(int $id)
    {
        if (permission('product-view')) {
            $this->setPageData('Product Details', 'Product Details', 'fas fa-paste', [['name' => 'Product', 'link' => route('product')], ['name' => 'Product Details']]);
            $product = $this->model->with('brand', 'category', 'tax', 'unit')->findOrFail($id);
            return view('product::details', compact('product'));
        } else {
            return $this->access_blocked();
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('product-delete')) {
            DB::beginTransaction();
            try {
                $total_sale_data = SaleProduct::where('product_id', $request->id)->get()->count();
                if ($total_sale_data > 0) {
                    $output = ['status' => 'error', 'message' => 'This data cannot delete because it is related with sales data.'];
                } else {
                    $result = $this->model->find($request->id)->delete();
                    $output = $this->delete_message($result);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $th->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function change_statuss(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-access')) {
                $result = $this->model->find($request->id)->update(['status' => $request->status]);
                $output = $result ? ['status' => 'success', 'message' => 'Status Has Been Changed Successfully']
                    : ['status' => 'error', 'message' => 'Failed To Change Status'];
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function bulkStatusChange(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-edit')) {
                $result = $this->model->whereIn('id', $request->product_ids)->update(['status' => $request->status]);
                $output = $result ? ['status' => 'success', 'message' => 'Status Has Been Changed Successfully']
                    : ['status' => 'error', 'message' => 'Failed To Change Status'];
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function generateProductCode()
    {
        $code = Keygen::numeric(8)->generate();
        if (DB::table('products')->where('code', $code)->exists()) {
            $this->generateProductCode();
        } else {
            return response()->json($code);
        }
    }

    public function generate_product_variant(Request $request)
    {
        if ($request->ajax()) {
            $combinations = $this->get_combinations($request->data);
            $product_id = $request->product_id;
            return view('product::variant-cobination', compact('combinations', 'product_id'))->render();
        }
    }

    private function get_combinations($arrays)
    {
        $result = array(array());
        foreach ($arrays as $property => $property_values) {
            $tmp = array();
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, array($property => $property_value));
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    public function product_variant_generate_code()
    {
        $code = Keygen::numeric(8)->generate();
        if (DB::table('product_variants')->where('item_code', $code)->exists()) {
            $this->product_variant_generate_code();
        } else {
            return response()->json($code);
        }
    }

    public function showImage(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-view')) {
                $product = $this->model->with('brand', 'category', 'tax', 'unit')->findOrFail($request->id);
                return view('product::modal-view', compact('product'))->render();
            }
        }
    }

    public function productLog(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-view')) {
                $sale = $this->model->with('user_activity')->findOrFail($request->id);
                return view('sale::log-data', compact('sale'))->render();
            }
        }
    }
}
