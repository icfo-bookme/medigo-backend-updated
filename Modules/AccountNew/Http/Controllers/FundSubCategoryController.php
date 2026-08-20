<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FundCategory;
use App\Models\FundInsert;
use App\Models\FundSubCategory;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class FundSubCategoryController extends Controller
{
    /**
     * Returns blade for category view
     */
    public function fundSubCategoryView(Request $request)
    {
        $id = $request->get('id');
        $categories = FundCategory::where('soft_delete', 0)->get();
        $data = [
            'categories' => $categories,
            'id' => $id
        ];
        return view('fundInsert.fundSubCategoryView', $data);
    }

   
    public function listAllFundSubCategories(Request $request)
    {
        $id = $request->get('id');
        $fundSubCategoryData = FundSubCategory::with(['category'])->where(['soft_delete' => 0])->orderBy('updated_at', 'desc');

        return Datatables::of($fundSubCategoryData)
           ->addColumn('created_by', function ($row) {
                return $row->createdBy ? $row->createdBy->name : '';
            })

            ->addColumn('updated_by', function ($row) {
                return $row->updatedBy ? $row->updatedBy->name : '';
            })
            ->addColumn('action', function ($data) {
                return '<button class="btn btn-primary btn-xs" title="Edit" onclick="fundSubCategoryEdit('.$data->id.')">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-xs" title="Delete" onclick="fundSubCategoryDelete('.$data->id.')">
                            <i class="fa fa-trash"></i>
                        </button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

   
     public function getFundSubCategoryById(Request $request)
{
    try {
        $id = $request->id;

        $subCategory = FundSubCategory::where('id', $id)
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



    public function fundSubCategoryInsert(Request $request)
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
                    'name' => Rule::unique('fund_sub_categories')->where(function ($query) use ($categoryId) {
                        return $query->where('category_id', $categoryId)->where('soft_delete',SOFT_DELETE_NO);
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
            $response = FundSubCategory::create([
                'category_id' => $request->category_id,
                'name'       => $request->name,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
                'soft_delete'=> SOFT_DELETE_NO

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

    /**
     *Display edit form
     */
    public function getFundSubCategoryEditForm(Request $request)
    {
        try{
            $categories = FundCategory::where('soft_delete',SOFT_DELETE_NO)->get();
            $subCategoryData = FundSubCategory::where('id',$request->get('id'))->first();

            if($subCategoryData){
                return response()->json([
                    'data' => view('admin.fundInsert.fundSubCategoryEditForm')->with([
                        'subCategoryData' => $subCategoryData,
                        'categories' => $categories,
                    ])->render(),
                    'status' => true,
                    'message' => 'successful'
                ]);
            }

            return response()->json([
                'data' => $subCategoryData,
                'status' => false,
                'message' => 'Form fetch failed! Please try again'
            ]);
        } catch(Exception $exception){
            Log::error($exception->getMessage());

            return response()->json([
                'data' => $exception->getMessage(),
                'status' => false,
                'message' => 'Something went wrong! Please try again'
            ]);
        }
    }

 
    public function fundSubCategoryUpdate(Request $request)
    {
        try{
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
                    'name' => Rule::unique('fund_sub_categories')->ignore($subcategoryId)->where(function ($query) use ($categoryId) {
                        return $query->where('category_id', $categoryId)->where('soft_delete',SOFT_DELETE_NO);
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

            $response = FundSubCategory::where('id',$request->subcategory_id)->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'updated_by' => auth()->user()->id
            ]);

            if($response){
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
        }catch (Exception $exception){
            Log::error($exception->getMessage());

            return response()->json([
                'data' => $exception->getMessage(),
                'status' => false,
                'message' => 'Something went wrong! Please try again'
            ]);
        }
    }

  
    public function fundSubCategoryDelete(Request $request)
    {
        try{
            //Update funds
            FundInsert::where('subcategory_id',$request->id)->update([
                'soft_delete' => SOFT_DELETE_YES
            ]);

            //Update fund sub categories
            $response = FundSubCategory::where('id',$request->id)->update([
            'soft_delete' => SOFT_DELETE_YES
            ]);

            if($response){
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
        }catch (Exception $exception){
            Log::error($exception->getMessage());

            return response()->json([
                'data' => $exception->getMessage(),
                'status' => false,
                'message' => 'Something went wrong! Please try again'
            ]);
        }
    }
}
