<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BaseCategory;
use App\Models\FundCategory;
use App\Models\FundInsert;
use App\Models\FundSubCategory;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class FundCategoryController extends Controller
{
    /**
     * Returns blade for category view
     */
    public function fundCategoryView(Request $request)
    {
        $base_categories = BaseCategory::all();
        $id = $request->get('id');

        return view('fundInsert.fundCategoryView', compact('id', 'base_categories'));
    }

    public function getFundCategoriesByBaseCategoryId(Request $request, $id)
    {
        try {


            $subCategories  = FundCategory::select('id', 'name')->where('base_category_id', $id)->where('soft_delete', 0)->get();

            if ($subCategories) {
                return response()->json([
                    'data'      => $subCategories,
                    'status'    => true,
                    'message'   => 'Successful'
                ]);
            }

            return response()->json([
                'data'      => $subCategories,
                'status'    => false,
                'message'   => 'Something happened wrong!'
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'data'      => $exception->getMessage(),
                'status'    => false,
                'message'   => 'Something went wrong! Please try again'
            ]);
        }
    }

    public function listAllFundCategories(Request $request)
    {
        $id = $request->query('id');
        $fundCategoryData = FundCategory::where(['soft_delete' => 0])->orderBy('updated_at', 'desc');

        return Datatables::of($fundCategoryData)

            ->addColumn('data_created_by', function ($data) {
                return optional($data->createdBy)->name ?? 'N/A';
            })
            ->addColumn('data_updated_by', function ($data) {
                return optional($data->updatedBy)->name ?? 'N/A';
            })
            ->addColumn('base_category', function ($data) {
                return optional($data->baseCategory)->name ?? 'N/A';
            })
            ->addColumn('action', function ($data) {
                return '<button class="bg-blue-500 text-white px-2 py-1 rounded" title="Edit" onclick="costCategoryEdit(' . $data->id . ')">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button class="bg-red-500 text-white px-2 py-1 rounded ml-2" title="Delete"
                                                onclick="costCategoryDelete(' . $data->id . ')">
                                               <i class="fa fa-trash"></i></button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function fundCategoryInsert(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    Rule::unique('fund_categories')->where(fn($q) => $q->where('soft_delete', 0)),
                ],
                'base_category_id' => 'required'
            ]);

            $category = FundCategory::create([
                'name' => $validated['name'],
                'base_category_id' => $validated['base_category_id'],
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Category created successfully',
                'data' => $category,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation-error',
                'message' => 'Category creation failed',
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            Log::error('Fund Category Insert Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!',
                'data' => $e->getMessage(),
            ]);
        }
    }

    public function getCostCategoryById(Request $request)
    {

        try {
            // Fetch category by ID and make sure it's not soft-deleted
            $category = FundCategory::where('id', $request->id)
                ->where('soft_delete', 0)
                ->first();

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Category fetched successfully',
                'data' => $category,
            ]);
        } catch (Exception $e) {
            Log::error('Get Cost Category Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong! Please try again',
                'data' => $e->getMessage(),
            ]);
        }
    }


    public function fundCategoryUpdate(Request $request)
    {
        try {
            $categoryId = $request->id; // match your form input name

            $validated = $request->validate([
                'id' => 'required|exists:fund_categories,id',
                'name' => [
                    'required',
                    Rule::unique('fund_categories')->ignore($categoryId)->where(fn($query) => $query->where('soft_delete', 0)),
                ],
            ], [
                'id.required' => 'Category ID is required',
                'id.exists' => 'Category not found',
                'name.required' => 'Category name is required',
                'name.unique' => 'This category already exists',
            ]);

            $updated = FundCategory::where('id', $categoryId)->update([
                'name' => $validated['name'],
                'updated_by' => auth()->user()->id
            ]);

            if ($updated) {
                return response()->json([
                    'status' => true,
                    'message' => 'Category updated successfully',
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Category update failed! Please try again',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation-error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ]);
        } catch (Exception $e) {
            Log::error('Cost Category Update Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong! Please try again',
                'data' => $e->getMessage(),
            ]);
        }
    }


    public function fundCategoryDelete(Request $request)
    {
        try {
            //Update fund subcategories
            // FundSubCategory::where('category_id', $request->id)->update([
            //     'soft_delete' => 1
            // ]);
            // //Update funds
            // FundInsert::where('category_id', $request->id)->update([
            //     'soft_delete' => 1
            // ]);

            $response = FundCategory::where('id', $request->id)->update([
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
