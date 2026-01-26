<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CategoryFormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Keygen\Keygen;
use Exception;

class CategoryController extends BaseController
{
    use UploadAble;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('product-category-access')) {
            $breadcrumb = [['name' => 'Product', 'link' => url('product')], ['name' => 'Category']];
            $this->setPageData('Category', 'Category', 'fas fa-th-list', $breadcrumb);
            return view('category.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function create()
    {
        if (permission('product-category-access')) {
            $breadcrumb = [['name' => 'Product', 'link' => url('product')], ['name' => 'Category']];
            $this->setPageData('Category', 'Category', 'fas fa-th-list', $breadcrumb);
            $categories = $this->model->where('parent_id', 0)->get();
            return view('category.create', compact('categories'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('product-category-access')) {
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

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';

                if (permission('product-category-edit')) {
                    $action .= ' <a class="dropdown-item" href="' . route("category.edit", $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
                if (permission('product-category-delete')) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }

                $status = permission('product-category-edit') ? change_status($value->id, $value->status, $value->name) : STATUS_LABEL[$value->status];
                $createdAt = $value->created_at ? date(config('settings.date_format'), strtotime($value->created_at)) : '';
                $updatedAt = $value->modified_by ? date(config('settings.date_format'), strtotime($value->updated_at)) : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No Update Date</span>';
                $modifiedBy = $value->modified_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Modified Yet</span>';

                $categorySerial = '<table class="border">
                        <tr>
                            <td class="text-nowrap"><b>Serial :</b></td>
                            <td>
                                <input type="number" name="nav_serial" class="form-control text-center input-group-sm"
                                 value="' . $value->nav_serial . '" oninput="updateCategorySerial(this.value, ' . $value->id . ')">
                            </td>
                        </tr>
                    </table>';

                $row = [];
                $row[] = $no;
                $row[] = $this->table_image(PRODUCT_IMAGE_PATH, $value->image, $value->name);
                $row[] = '<div class="text-left"><b>Name: </b>' . $value->name . '<br><b>Slug: </b>' . $value->slug . '</div>';
                $row[] = $value->parentCategory
                    ? '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">' . $value->parentCategory->name . '</span>'
                    : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">N/A</span>';
                $row[] = $categorySerial;
                $row[] = '<div class="text-left"><b>Status: </b>' . $status . '<br><br><b>Created At: </b>' . $createdAt . '<br><b>Updated At: </b>' . $updatedAt . '<br><br><b>Modified By: </b>' . $modifiedBy . '</div>';
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function updateSerial(Request $request)
    {
        if ($request->ajax() && permission('product-category-edit')) {
            // Check if the requested nav_serial is already assigned to another category
            $existingCategory = $this->model->where('nav_serial', $request->nav_serial)
                ->where('id', '!=', $request->category_id)
                ->first();

            if ($existingCategory) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The requested serial number is already assigned to the category "' . $existingCategory->name . '".'
                ]);
            }

            // Proceed with update if no other category has the same nav_serial
            $result = $this->model->find($request->category_id)->update(['nav_serial' => $request->nav_serial]);
            $output = $result
                ? ['status' => 'success', 'message' => 'Serial has been updated successfully.']
                : ['status' => 'error', 'message' => 'Failed to update serial.'];

            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(CategoryFormRequest $request)
    {
        if ($request->ajax() && permission('product-category-add') || permission('product-category-edit')) {
            DB::beginTransaction();
            try {
                $collection = collect($request->all());
                $collection = $this->track_data($collection, $request->update_id);
                $image = $request->image;
                if ($request->hasFile('image')) {
                    $image = $this->upload_file($request->file('image'), PRODUCT_IMAGE_PATH);
                    if (!empty($request->old_image)) {
                        $this->delete_file($request->old_image, PRODUCT_IMAGE_PATH);
                    }
                }
                $slugs = Str::slug($request->name . Str::random(10), '-');
                $collection = $collection->merge(compact('image'));
                $result = $this->model->updateOrCreate(['id' => $request->update_id, 'slug' => $slugs], $collection->all());

                $output = $this->store_message($result, null);
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if (permission('product-category-edit')) {
            $this->setPageData('Category Edit', 'Category Edit', 'fab fa-opencart', [['name' => 'Category Edit']]);
            $data = $this->model->findOrFail($request->id);
            $categories = $this->model->where('parent_id', 0)->get();
            return view('category.edit', compact('data', 'categories'));
        } else {
            $output = $this->unauthorized();
        }
        return response()->json($output);
    }

    public function update(Request $request)
    {
        if ($request->ajax() && permission('product-category-add')) {
            DB::beginTransaction();
            try {
                $collection = collect($request->all());
                $collection = $this->track_data($collection, $request->update_id);
                $image = $request->old_image;

                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $extension = $file->getClientOriginalExtension();
                    $filenameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $originalFilename = Str::slug($filenameWithoutExtension, '-');

                    $filename = $originalFilename . '.' . $extension;

                    $image_path = 'storage/' . PRODUCT_IMAGE_PATH . $filename;
                    $image = $filename;
                    Image::make($file)->save(public_path($image_path), 50);

//                    $image = $this->upload_file($request->file('image'), PRODUCT_IMAGE_PATH);
                    if (!empty($request->old_image)) {
                        $this->delete_file($request->old_image, PRODUCT_IMAGE_PATH);
                    }
                }
                $collection = $collection->merge(compact('image'));
                $result = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
                $output = $this->store_message($result, $request->update_id);
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

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('product-category-delete')) {
            $category = $this->model->find($request->id);
            $this->delete_file($category->image, PRODUCT_IMAGE_PATH);
            $category->delete();
            $output = $this->delete_message($category);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if ($request->ajax() && permission('product-category-bulk-delete')) {
            $result = $this->model->destroy($request->ids);
            $output = $this->bulk_delete_message($result);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if ($request->ajax() && permission('product-category-edit')) {
            $result = $this->model->find($request->id)->update(['status' => $request->status]);
            $output = $result ? ['status' => 'success', 'message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error', 'message' => 'Failed To Change Status'];
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

}
