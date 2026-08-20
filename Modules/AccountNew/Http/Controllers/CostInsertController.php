<?php

namespace App\Http\Controllers\Account;

use App\Models\CostCategory;
use App\Models\CostInsert;
use App\Models\CostSubCategory;
use App\Models\CostInsertAudit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BaseCategory;
use App\Models\CashStoragePlatform;
use App\Models\CostEditReason;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Exception;

class CostInsertController extends Controller
{


    public function costInsertView(Request $request)
    {

        $baseCategories = BaseCategory::where('soft_delete', 0)->get();

        return view('costinsert.costInsertView', compact('baseCategories'));
    }

    public function getSubcategoriesByCategoryId($id)
    {
        $subCategories = CostSubCategory::where('category_id', $id)->where('soft_delete', 0)->get();
        return response()->json([
            'data' => $subCategories,
            'status' => true,
            'message' => 'Successful'
        ]);
    }

    public function getCategoriesByBaseCategoryId($id)
    {
        $categories = CostCategory::where('base_category_id', $id)->where('soft_delete', 0)->get();
        return response()->json([
            'data' => $categories,
            'status' => true,
            'message' => 'Successful'
        ]);
    }

    public function listAllInsertedCosts(Request $request)
    {
        $role = auth()->user()->role;
        $userId = auth()->user()->id;

        $costInsertData = CostInsert::with([
            'category',
            'subcategory',
            'baseCategory',
            'createdBy',
            'updatedBy'
        ])
            ->where('cost_inserts.soft_delete', 0)
            ->orderBy('cost_inserts.created_at', 'desc');

        return Datatables::of($costInsertData)

            // Base Category
            ->addColumn('base_category_name', function ($data) {
                return $data->baseCategory->name ?? '';
            })

            // Category
            ->addColumn('category_name', function ($data) {
                return $data->category->name ?? '';
            })

            // Subcategory
            ->addColumn('subcategory_name', function ($data) {
                return $data->subcategory->name ?? '';
            })

            // Created At
            ->editColumn('created_at', function ($data) {
                return $data->created_at
                    ? $data->created_at->format('Y-m-d h:i A')
                    : '';
            })

            // Approved By badges
            ->addColumn('approved_by', function ($data) {

                $badge = function ($status, $label, $title) {
                    $color = $status
                        ? 'bg-green-100 border border-green-700 text-green-700'
                        : 'bg-yellow-100 text-yellow-700';

                    return '<span title="' . $title . '" 
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mr-1 ' . $color . '">
                        ' . $label . '
                    </span>';
                };

                return
                    $badge($data->is_approved_by_superadmin, 'S', 'Super Admin') .
                    $badge($data->is_approved_by_hop, 'H', 'HOP') .
                    $badge($data->is_approved_by_manager, 'M', 'Manager') .
                    $badge($data->is_approved_by_accounts, 'A', 'Accounts') .
                    $badge($data->is_approved_by_opManager, 'O', 'Operation Manager');
            })

            // Created By
            ->addColumn('created_by_name', function ($data) {
                return $data->createdBy->name ?? '';
            })

            // Updated By
            ->addColumn('updated_by_name', function ($data) {
                return $data->updatedBy->name ?? '';
            })

            // ACTION COLUMN (UPDATED LOGIC)
            ->addColumn('action', function ($data) use ($role, $userId) {

                $jsonData = htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');

                $createdTime = $data->created_at;
                $now = \Carbon\Carbon::now();
                $diffInMinutes = $createdTime->diffInMinutes($now);

                $isSuperAdmin = ($userId == env('SUPERADMIN_ID'));
                $canEdit = $isSuperAdmin || $diffInMinutes <= 30;

                // ---------------- EDIT BUTTON ----------------
                if ($isSuperAdmin) {
                    $editButton = '
                    <button class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-2 py-1 rounded mr-1"
                        title="Edit"
                        onclick="costEdit(' . $jsonData . ')">
                        ✏
                    </button>';
                } else {
                    if ($canEdit) {
                        $editButton = '
                        <button class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-2 py-1 rounded mr-1"
                            title="Edit"
                            onclick="costEdit(' . $jsonData . ')">
                            ✏
                        </button>';
                    } else {
                        $editButton = '
                        <button class="bg-gray-400 text-white text-xs px-2 py-1 rounded mr-1 cursor-not-allowed"
                            title="Edit locked after 30 minutes"
                            onclick="editLockedMessage()">
                            ✏
                        </button>';
                    }
                }

                // ---------------- APPROVAL ----------------
                $approveCheck = '';

                if ($role != null) {
                    $fieldName = "is_approved_by_" . $role;

                    if ($data->$fieldName == 1) {
                        $approveCheck = '
                        <button class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded mr-1"
                            title="Not Approve"
                            onclick="approvalStatusChange(' . $data->id . ',0)">
                            ✖
                        </button>';
                    } else {
                        $approveCheck = '
                        <button class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-2 py-1 rounded mr-1"
                            title="Approve"
                            onclick="approvalStatusChange(' . $data->id . ',1)">
                            ✔
                        </button>';
                    }
                }

                // ---------------- RETURN ACTION ----------------
                if (
                    $userId == env('SUPERADMIN_ID') ||
                    $userId == env('HOP_ID') ||
                    $userId == env('ACCOUNTS_ID') ||
                    $userId == env('MANAGER_ID') ||
                    $userId == env('OPERATION_MANAGER_ID')
                ) {
                    return '
                    <div class="flex items-center">

                        ' . $editButton . '

                        <button class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded mr-1"
                            title="Delete"
                            onclick="costDelete(' . $data->id . ')">
                            🗑
                        </button>

                        ' . $approveCheck . '

                        <button class="bg-green-500 hover:bg-green-600 text-white text-xs px-2 py-1 rounded"
                            title="Details"
                            onclick="costDetails(' . $data->id . ')">
                            ℹ
                        </button>

                    </div>
                ';
                }

                return '';
            })

            // Allow HTML rendering
            ->rawColumns(['approved_by', 'action'])
            ->make(true);
    }


    public function costInsert(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'category_id' => 'required',
                'subcategory_id' => 'required',
                'amount' => 'required|numeric|min:0',
                'base_category_id' => 'required',
                'date' => 'nullable|date',
                'description' => 'nullable|string|max:1000',
            ]);

            // Create new CostInsert
            $cost = CostInsert::create([
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'amount' => $request->amount,
                'date' => $request->date ?? now()->format('Y-m-d'),
                'description' => $request->description,
                'base_category_id' => $request->base_category_id,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
                'soft_delete' => 0,
            ]);

            // if ($cost) {
            //     $inCash = CashStoragePlatform::where('name', 'Office Cash')->first();
            //     $inCash->update([
            //         'amount' => $inCash->amount - $cost['amount'],
            //         'updated_by' => auth()->id(),
            //     ]);
            // }

            if ($cost) {
                CostInsertAudit::create([
                    'trigger_type' => 'New insert',
                    'cost_id' => $cost->id,
                    'category_id' => $cost->category_id,
                    'subcategory_id' => $cost->subcategory_id,
                    'amount' => $cost->amount,
                    'date' => $cost->date,
                    'description' => $cost->description,
                    'created_by' => $cost->created_by,
                    'updated_by' => $cost->updated_by,
                    'soft_delete' => $cost->soft_delete,
                    'created_at' => $cost->created_at,
                    'updated_at' => $cost->updated_at,
                    'cost_id' => $cost->id
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => 'Cost saved successfully',
                'data' => $cost,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors
            return response()->json([
                'status' => 'validation-error',
                'message' => 'Cost creation failed!',
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log and return general errors
            Log::error($e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong! Please try again.',
                'data' => $e->getMessage(),
            ], 500);
        }
    }


    public function showCostEditReasonPage(Request $request)
    {
        $html = view('admin.costEditReason.costEditReasons', [
            'cost_insert_id' => $request->id
        ])->render();

        return response()->json([
            'status' => true,
            'data' => $html
        ]);
    }


    public function getCostEditReasonDetails(Request $request)
    {
        $query = CostEditReason::with(['category', 'subcategory'])
            ->where('cost_insert_id', $request->id)
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('category', function ($item) {
                return $item->category->name ?? 'N/A';
            })
            ->addColumn('subcategory', function ($item) {
                return $item->subcategory->name ?? 'N/A';
            })
            ->addColumn('created_at', function ($item) {
                return $item->created_at->format('Y-m-d H:i:s');
            })
            ->make(true);
    }

    /**
     *Display log form
     */
    public function getCostLogForm(Request $request)
    {
        try {
            $costInsertData = CostInsert::with(['category', 'subcategory'])->where(['cost_inserts.soft_delete' => 0, 'id' => $request->id])->orderBy('cost_inserts.updated_at', 'desc')->get();
            $costLogData = CostInsertAudit::with(['category', 'subcategory'])->where(['trigger_type' => 'before_update', 'cost_id' => $request->id])->orderBy('updated_at', 'desc')->latest()->first()->get();
            $final = $costInsertData->merge($costLogData);
            // dd($costLogData);

            if ($costInsertData) {
                return response()->json([
                    'data' => view('admin.costinsert.costLogForm')->with([
                        'final' => $final,


                    ])->render(),
                    'status' => true,
                    'message' => 'successful'
                ]);
            }

            return response()->json([
                'data' => $costInsertData,
                'status' => false,
                'message' => 'Form fetch failed! Please try again'
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


    public function costUpdate(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'category_id' => 'required',
                'subcategory_id' => 'required',
                'amount' => 'required|numeric',
                'reason' => 'required|string',
                'date' => 'nullable|date',
                'description' => 'nullable|string',
                'id' => 'required|exists:cost_inserts,id'
            ]);

            $cost = CostInsert::findOrFail($validated['id']);

            if ($cost) {
                CostInsertAudit::create([
                    'trigger_type' => 'before_update',
                    'cost_id' => $cost->id,
                    'category_id' => $cost->category_id,
                    'subcategory_id' => $cost->subcategory_id,
                    'amount' => $cost->amount,
                    'date' => $cost->date,
                    'description' => $cost->description,
                    'is_approved_by_superadmin' => $cost->is_approved_by_superadmin,
                    'is_approved_by_hop' => $cost->is_approved_by_hop,
                    'is_approved_by_manager' => $cost->is_approved_by_manager,
                    'is_approved_by_accounts' => $cost->is_approved_by_accounts,
                    'is_approved_by_all' => $cost->is_approved_by_all,
                    'created_by' => $cost->created_by,
                    'updated_by' => $cost->updated_by,
                    'soft_delete' => $cost->soft_delete,
                    'created_at' => $cost->created_at,
                    'updated_at' => $cost->updated_at,
                    'cost_id' => $cost->id
                ]);
            }
            $prevAmount = $cost->amount;

            $updateData = [
                'category_id' => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'],
                'date' => $validated['date'] ?? $cost->date,
                'description' => $validated['description'] ?? $cost->description,
                'updated_by' => auth()->user()->id,
            ];

            // $inCash = CashStoragePlatform::where('name', 'Office Cash')->first();
            // if ($prevAmount > $validated['amount']) {
            //     $inCash->update([
            //         'amount' => $inCash->amount - ($validated['amount'] - $prevAmount),
            //         'updated_by' => auth()->id(),
            //     ]);
            // } else if ($prevAmount < $validated['amount']) {
            //     $inCash->update([
            //         'amount' => $inCash->amount + ($prevAmount - $validated['amount']),
            //         'updated_by' => auth()->id(),
            //     ]);
            // }

            // If amount changed, reset approvals
            if ($prevAmount != $validated['amount']) {
                $updateData['amount'] = $validated['amount'];
                $updateData = array_merge($updateData, [
                    'is_approved_by_superadmin' => 0,
                    'is_approved_by_hop' => 0,
                    'is_approved_by_manager' => 0,
                    'is_approved_by_accounts' => 0,
                    'is_approved_by_opManager' => 0,
                    'is_approved_by_all' => 0,
                ]);
            }

            $cost->update($updateData);

            // Save edit reason
            CostEditReason::create([
                'cost_insert_id' => $cost->id,
                'category_id' => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'],
                'amount' => $validated['amount'],
                'prev_amount' => $prevAmount,
                'date' => $validated['date'] ?? $cost->date,
                'description' => $validated['description'] ?? $cost->description,
                'reason' => $validated['reason'],
                'created_by' => auth()->user()->id,
            ]);


            return response()->json([
                'success' => true,
                'status' => true,
                'message' => 'Cost updated successfully',
                'data' => $cost
            ]);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Something went wrong! Please try again',
                'data' => $e->getMessage()
            ]);
        }
    }


    public function costDelete(Request $request)
    {
        try {
            $response = CostInsert::where('id', $request->id)->update([
                'soft_delete' => 1
            ]);

            if ($response) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cost successfully removed',
                    'data' => $response
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Cost removing failed! Please try again',
                'data' => null
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'data' => $exception->getMessage(),
                'success' => false,
                'message' => 'Something went wrong! Please try again'
            ]);
        }
    }


    public function approvalStatusChange(Request $request, $id)
    {

        $role = auth()->user()->role;
        $fieldName = "is_approved_by_" . $role;
        try {
            $costId = $id;
            $approvalStatus = $request->status;
            if ($approvalStatus == 1) {
                //Make approved


                CostInsert::where('id', $costId)->update([
                    $fieldName => 1
                ]);

                $costData = CostInsert::where('id', $costId)->first();
                if ($costData->is_approved_by_superadmin == 1 && $costData->is_approved_by_hop == 1 && $costData->is_approved_by_manager == 1 && $costData->is_approved_by_accounts == 1 && $costData->is_approved_by_opManager == 1) {
                    CostInsert::where('id', $costData->id)->update([
                        'is_approved_by_all' => 1
                    ]);
                }
                return response()->json([
                    'data' => null,
                    'success' => true,
                    'message' => "Successfully approved"
                ]);
            } else {
                //Disprove

                CostInsert::where('id', $costId)->update([
                    $fieldName => 0,
                    'is_approved_by_all' => 0
                ]);

                return response()->json([
                    'data' => null,
                    'status' => true,
                    'success' => true,
                    'message' => "Approval cancelled"
                ]);
            }
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
