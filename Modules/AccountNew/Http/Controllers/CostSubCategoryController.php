<?php

namespace App\Http\Controllers\Account;

use App\CostInsert\CostInsert;
use App\Models\CostSubCategory;
use App\Models\CostCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class CostSubCategoryController extends Controller
{
   
    public function costSubCategoryView()
    {
        

        $categories = CostCategory::where('soft_delete', 0)->get();

        $data = [
            'categories' => $categories,
        ];
        return view('costinsert.costSubCategoryView', $data);
    }

   
    public function listAllCostSubCategories(Request $request)
    {
        $id = $request->query('id');

        $costSubCategoryData = CostSubCategory::with(['category'])->where(['soft_delete' => 0])->where('base_category_id', $id)->orderBy('updated_at', 'desc');

        return Datatables::of($costSubCategoryData)
            ->addColumn('created_by', function ($row) {
                return $row->createdBy ? $row->createdBy->name : '';
            })

            ->addColumn('updated_by', function ($row) {
                return $row->updatedBy ? $row->updatedBy->name : '';
            })
            ->addColumn('action', function ($data) {
                return '<button class="bg-blue-500 text-white px-2 py-1 rounded" title="Edit" onclick="costSubCategoryEdit(' . $data->id . ')">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button class="bg-red-500 text-white px-2 py-1 rounded ml-2" title="Delete"
                                                onclick="costSubCategoryDelete(' . $data->id . ')">
                                               <i class="fa fa-trash"></i></button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

  
    public function costSubCategoryInsert(Request $request)
    {
        try {
            $categoryId = $request->category_id;
            $attributeNames = array(
                'name' => $request->name,
                'category_id' => $categoryId
            );

            //validating the attributes
            $validator = Validator::make($attributeNames, [
                'category_id' => 'required',
                'name' => [
                    'required',
                    'name' => Rule::unique('cost_sub_categories')->where(function ($query) use ($categoryId) {
                        return $query->where('category_id', $categoryId)->where('soft_delete', 0);
                    }),
                ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'data' => $validator->getMessageBag()->toArray(),
                    'status' => "validation-error",
                    'message' => "Sub Category creation failed!"
                ]);
            }

            //Inserting data
            $response = CostSubCategory::create([
                'category_id' => $request->category_id,
                'name'       => $request->name,
                'base_category_id' => $request->base_category_id,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
                'soft_delete' => 0

            ]);

            if ($response) {
                return response()->json([
                    'data' => $response,
                    'status' => true,
                    'message' => 'Sub Catgeory created succesfully'
                ]);
            }

            return response()->json([
                'data' => $response,
                'status' => false,
                'message' => 'SubCatgeory creation failed! Please try again'
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json([
                'data' => $exception->getMessage(),
                'status' => false,
                'message' => 'Something went wrong! Please try again'
            ]);
        }
    }

  
    public function getSubCatergoryById(Request $request)
    {
        try {
            $id = $request->id;

            $subCategory = CostSubCategory::where('id', $id)
                ->where('soft_delete', 0)
                ->first();

            if (!$subCategory) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sub Category not found',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Sub Category fetched successfully',
                'data' => $subCategory
            ]);
        } catch (Exception $e) {
            Log::error('Get Sub Category Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong! Please try again',
                'data' => $e->getMessage(),
            ]);
        }
    }


    public function costSubCategoryUpdate(Request $request)
    {
        try {
            $subcategoryId = $request->subcategory_id;
            $categoryId = $request->category_id;
            //gettings attributes
            $attributeNames = array(
                'subcategory_id' => $subcategoryId,
                'category_id' => $categoryId,
                'name' => $request->name,
            );

            //validating the attributes
            $validator = Validator::make($attributeNames, [
                'name' => [
                    'required',
                    'name' => Rule::unique('cost_sub_categories')->ignore($subcategoryId)->where(function ($query) use ($categoryId) {
                        return $query->where('category_id', $categoryId)->where('soft_delete', 0);
                    }),
                ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'data' => $validator->getMessageBag()->toArray(),
                    'status' => "validation-error",
                    'message' => "Category update failed"
                ]);
            }

            $response = CostSubCategory::where('id', $request->subcategory_id)->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'updated_by' => auth()->user()->first_name
            ]);

            if ($response) {
                return response()->json([
                    'data' => $response,
                    'status' => true,
                    'message' => 'Sub Category updated successfully'
                ]);
            }
            return response()->json([
                'data' => $response,
                'status' => false,
                'message' => 'Sub Category update failed! Please try again'
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'data' => $exception->getMessage(),
                'status' => false,
                'message' => 'Something went wrong! Please try again'
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Deletes category (Soft delete)
     */
    public function costSubCategoryDelete(Request $request)
    {
        try {
            //Update costs
            // CostInsert::where('subcategory_id', $request->id)->update([
            //     'soft_delete' => SOFT_DELETE_YES
            // ]);

            //Update cost sub categories
            $response = CostSubCategory::where('id', $request->id)->update([
                'soft_delete' => 1
            ]);

            if ($response) {
                return response()->json([
                    'status' => true,
                    'message' => 'Category successfully removed',
                    'data' => $response
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Category removing failed! Please try again',
                'data' => null
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'data' => $exception->getMessage(),
                'status' => false,
                'message' => 'Something went wrong! Please try again'
            ]);
        }
    }
}
