<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BaseCategory;
use App\Models\CashStoragePlatform;
use App\Models\FundCategory;
use App\Models\FundInsert;
use App\Models\FundInsertAudit;
use App\Models\FundSubCategory;
use Exception;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class FundInsertController extends Controller
{


    public function getFundSubcategoriesByCategoryId(Request $request)
    {
        try {
            $categoryId     = $request->id;

            $subCategories  = FundSubCategory::select('id', 'name')->where('category_id', $categoryId)->where('soft_delete', 0)->get();

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

    public function getFundcategoriesBybaseCategoryId(Request $request)
    {
        try {
            $Id     = $request->id;

            $Categories  = FundCategory::select('id', 'name')->where('base_category_id', $Id)->where('soft_delete', 0)->get();

            if ($Categories) {
                return response()->json([
                    'data'      => $Categories,
                    'status'    => true,
                    'message'   => 'Successful'
                ]);
            }

            return response()->json([
                'data'      => $Categories,
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


    public function fundInsertView()
    {
        $baseCategories = BaseCategory::where('soft_delete', 0)->get();

        $data = [
            'baseCategories' => $baseCategories
        ];
        return view('fundInsert.fundInsertView', $data);
    }


    public function listAllInsertedFunds(Request $request)
    {

        $fundInsertData = FundInsert::with(['category', 'subcategory'])->where(['fund_inserts.soft_delete' => 0])->orderBy('fund_inserts.updated_at', 'desc');
        return Datatables::of($fundInsertData)
            ->addColumn('data_category_name', function ($data) {
                return $data->category->name;
            })
            ->addColumn('base_category', function ($data) {
                return $data->baseCategory->name;
            })
            ->addColumn('data_subcategory_name', function ($data) {
                if ($data->subcategory == null) {
                    return "Not available";
                } else {
                    return $data->subcategory->name;
                }
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at->format('Y-m-d h:i A');
            })
            ->addColumn('action', function ($data) {
                $userId  = auth()->user()->id;
                if (($userId == env('SUPERADMIN_ID') || $userId == env('HOP_ID') || $userId == env('ACCOUNTS_ID') || $userId == env('MANAGER_ID') || $userId == env('OPERATION_MANAGER_ID'))) {
                    return '<button class="bg-blue-500 text-white px-2 py-1 rounded" title="Edit" onclick="fundEdit(' . $data->id . ')">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button class="bg-red-500 text-white px-2 py-1 rounded ml-2" title="Delete" onclick="fundDelete(' . $data->id . ')">
                                <i class="fa fa-trash"></i>
                            </button>';
                }
            })
            ->rawColumns(['action', 'data_category_name', 'data_subcategory_name'])
            ->make(true);
    }


    public function fundInsert(Request $request)
    {
        try {

            $validated = $request->validate([
                'category_id'    => ['required', 'integer'],
                'subcategory_id' => ['nullable', 'integer'],
                'base_category_id' => ['required', 'integer'],
                'amount'         => ['required', 'numeric'],
                'date'           => ['required', 'date'],
                'description'    => ['nullable', 'string'],
            ]);


            $fund = FundInsert::create([
                'category_id'    => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'] ?? null,
                'base_category_id' => $validated['base_category_id'],
                'amount'         => $validated['amount'],
                'date'           => $validated['date'],
                'description'    => $validated['description'] ?? null,
                'created_by'     => auth()->id(),
                'updated_by'     => auth()->id(),
                'soft_delete'    => 0,
            ]);

            // $inCash = CashStoragePlatform::where('name', 'Office Cash')->first();

            // $inCash->update([
            //     'amount' => $inCash->amount + $validated['amount'],
            //     'updated_by' => auth()->id(),
            // ]);

            if($fund){
                FundInsertAudit::create([
                    'trigger_type' => 'New Insert',
                    'fund_id' => $fund->id,
                    'category_id' => $validated['category_id'],
                    'subcategory_id' => $validated['subcategory_id'] ?? null,
                    'amount' => $validated['amount'],
                    'date' =>$validated['date'],
                    'description' => $validated['description'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                    'soft_delete' => 0,
            ]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Fund created successfully',
                'data'    => $fund
            ], 201);
        } catch (\Throwable $e) {

            Log::error('Fund Insert Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     *Display edit form
     */
    public function getFundEditForm(Request $request)
    {
        try {

            $fundData = FundInsert::find($request->id);

            if (!$fundData) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Fund not found'
                ], 404);
            }

            $categories = FundCategory::where('soft_delete', 0)->get();

            $fundSubCategories = FundSubCategory::where('category_id', $fundData->category_id)
                ->where('soft_delete', 0)
                ->get();

            return response()->json([
                'status'        => true,
                'message'       => 'success',
                'data'          => $fundData,
                'categories'    => $categories,
                'subcategories' => $fundSubCategories,
            ]);
        } catch (\Throwable $e) {

            Log::error('Fund Edit Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function fundUpdate(Request $request)
    {
        try {


            $validated = $request->validate([
                'id' => ['required', 'integer'],
                'category_id'    => ['required', 'integer'],
                'subcategory_id' => ['required', 'integer'],
                'amount'         => ['required', 'numeric'],
                'date'           => ['required', 'date'],
                'description'    => ['nullable', 'string'],
            ]);

            //  Find record first (safe)
            $fund = FundInsert::find($validated['id']);
            // $inCash = CashStoragePlatform::where('name', 'Office Cash')->first();
            // if ($fund && $fund->amount > $validated['amount']) {
            //     $inCash->update([
            //         'amount' => $inCash->amount - ($fund->amount - $validated['amount']),
            //         'updated_by' => auth()->id(),
            //     ]);
            // } elseif ($fund && $fund->amount < $validated['amount']) {
            //     $inCash->update([
            //         'amount' => $inCash->amount + ($validated['amount'] - $fund->amount),
            //         'updated_by' => auth()->id(),
            //     ]);
            // } else {
            //     return response()->json([
            //         'status'  => false,
            //         'message' => 'Your previous and current amount is same'
            //     ], 404);
            // }

            if (!$fund) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Fund not found'
                ], 404);
            }

            if($fund){
                FundInsertAudit::create([
                    'trigger_type' => 'Before Update',
                    'fund_id' => $fund->id,
                    'category_id' => $fund['category_id'],
                    'subcategory_id' => $fund['subcategory_id'] ?? null,
                    'amount' => $fund['amount'],
                    'date' =>$fund['date'],
                    'description' => $fund['description'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                    'soft_delete' => 0,
            ]);
            }

            //  Update
            $fund->update([
                'category_id'    => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'],
                'amount'         => $validated['amount'],
                'date'           => $validated['date'],
                'description'    => $validated['description'] ?? null,
                'updated_by'     => auth()->id(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Fund updated successfully',
                'data'    => $fund
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Fund Update Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function fundDelete(Request $request)
    {
        try {
            $response = FundInsert::where('id', $request->id)->update([
                'soft_delete' => 1
            ]);

            if ($response) {
                return response()->json([
                    'status'    => true,
                    'message'   => 'Fund successfully removed',
                    'data'      => $response
                ]);
            }

            return response()->json([
                'status'    => false,
                'message'   => 'Fund removing failed! Please try again',
                'data'      => null
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
}
