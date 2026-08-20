<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\CashStoragePlatform;
use App\Models\CostInsert;
use App\Models\FundInsert;
use App\Models\Reinvestment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class EndingBlanceController extends Controller
{
    public function endingBlanceView()
    {
        return view('account.endingBlance.index');
    }

    public function getEndingBlance($id)
    {
        try {
            $data = CashStoragePlatform::findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data not found'
            ], 404);
        }
    }

    public function listAllEndingBlance(Request $request)
    {
        $query = CashStoragePlatform::where('soft_delete', 0)->orderBy('updated_at', 'desc');
        $totalAmount = (clone $query)->sum('amount');

        $reInvestmentAmount = Reinvestment::where('soft_delete', 0)->sum('amount');
        $addFundAmount = FundInsert::where('soft_delete', 0)->sum('amount');
        $addCostAmount = CostInsert::where('soft_delete', 0)->sum('amount');
       
        $sellAmount = Sale::where('soft_delete', 0)->sum('total_paid_amount');
    //    dd($reInvestmentAmount, $addFundAmount, $addCostAmount, $sellAmount);
        $sumOfCash = $reInvestmentAmount + $addFundAmount + $sellAmount - $addCostAmount ;

        if($totalAmount != $sumOfCash){
            $result = "Not Match";
        }else{
            $result = "Match";  
        }

        $totalInvestment = Reinvestment::where('soft_delete', 0)->sum('amount');
        $totalExpense = CostInsert::where('soft_delete', 0)->sum('amount');
        $totalFund = FundInsert::where('soft_delete', 0)->sum('amount');

        return DataTables::of($query)

            ->addColumn('action', function ($data) {
                $userId = auth()->user()->id;
                if (($userId == env('SUPERADMIN_ID') || $userId == env('HOP_ID') || $userId == env('ACCOUNTS_ID') || $userId == env('MANAGER_ID') || $userId == env('OPERATION_MANAGER_ID'))) {
                    return '<button class="bg-blue-500 text-white px-2 py-1 rounded" title="Edit" onclick="cashStoragePlatformEdit(' . $data->id . ')">
                                <i class="fa fa-pencil"></i>
                            </button>   
                            <button class="bg-red-500 text-white px-2 py-1 rounded ml-2" title="Delete" onclick="cashStoragePlatformDelete(' . $data->id . ')">
                                <i class="fa fa-trash"></i>
                            </button>';
                }
            })
            ->rawColumns(['action', 'name', 'amount'])
            ->with('total_amount', $totalAmount)
            ->with('sum_of_cash', $sumOfCash)
            ->with('result', $result)
            ->make(true);
    }
    public function endingBlanceInsert(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string'],
                'amount' => ['required', 'numeric'],
            ]);
            $endingBlance = CashStoragePlatform::create([
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'soft_delete' => 0,
            ]);
            return response()->json([
                'success' => true,
                'status' => true,
                'message' => 'Cash Storage Platform created successfully',
                'data' => $endingBlance
            ]);
        } catch (\Throwable $e) {
            Log::error('Cash Storage Platform Insert Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function updateEndingBlance(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => ['required', 'exists:cash_storage_platforms'],
                'name' => ['required', 'string'],
                'amount' => ['required', 'numeric'],
            ]);
            $cashStoragePlatform = CashStoragePlatform::findOrFail($validated['id']);
            $cashStoragePlatform->update([
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'updated_by' => auth()->id(),
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Cash Storage Platform updated successfully',
                'data' => $cashStoragePlatform
            ]);
        } catch (\Throwable $e) {
            Log::error('Cash Storage Platform Update Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function deleteEndingBlance(Request $request)
    {
        try {
            $response = CashStoragePlatform::where('id', $request->id)->update([
                'soft_delete' => 1
            ]);
            if ($response) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cash Storage Platform successfully removed',
                    'data' => $response
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Cash Storage Platform Delete Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cash Storage Platform removing failed! Please try again',
                'data' => $e->getMessage()
            ]);
        }
    }
}
