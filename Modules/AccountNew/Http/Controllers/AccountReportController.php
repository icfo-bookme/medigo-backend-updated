<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BaseCategory;
use App\Models\CostInsert;
use App\Models\FundInsert;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AccountReportController extends Controller
{
    public function accountReportView()
    {
        $baseCategories = BaseCategory::where('soft_delete', 0)
            ->get();
        return view('account.report.costReportView', compact('baseCategories'));
    }

    public function fundReportView()
    {
        $baseCategories = BaseCategory::where('soft_delete', 0)
            ->get();
        return view('account.report.fundReportView', compact('baseCategories'));
    }



    public function expenseReport(Request $request)
    {
        $query = CostInsert::with([
            'baseCategory',
            'category',
            'subcategory',
            'createdBy',
            'updatedBy'
        ])
            ->where('cost_inserts.soft_delete', 0);

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('cost_inserts.created_at', [
                $request->from_date . " 00:00:00",
                $request->to_date . " 23:59:59"
            ]);
        }

        if ($request->base_category_id) {
            $query->where('base_category_id', $request->base_category_id);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->subcategory_id) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        $totalAmount = (clone $query)->sum('amount');

        return DataTables::of($query)

            ->addColumn('base_category_name', fn($data) => $data->baseCategory->name ?? '')
            ->addColumn('category_name', fn($data) => $data->category->name ?? '')
            ->addColumn('subcategory_name', fn($data) => $data->subcategory->name ?? '')

            ->editColumn('created_at', function ($data) {
                return $data->created_at
                    ? $data->created_at->format('Y-m-d h:i A')
                    : '';
            })

            ->addColumn('created_by_name', fn($data) => $data->createdBy->name ?? '')

            ->with('total_amount', $totalAmount)

            ->make(true);
    }

    public function fundReport(Request $request)
    {


        $query = FundInsert::with(['category', 'subcategory', 'baseCategory'])->where(['fund_inserts.soft_delete' => 0])->orderBy('fund_inserts.updated_at', 'desc');


        if ($request->from_date && $request->to_date) {
            $query->whereBetween('fund_inserts.created_at', [
                $request->from_date . " 00:00:00",
                $request->to_date . " 23:59:59"
            ]);
        }
        if ($request->base_category_id) {
            $query->where('base_category_id', $request->base_category_id);
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->subcategory_id) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        $totalAmount = (clone $query)->sum('amount');

        return Datatables::of($query)
            ->addColumn('category_name', function ($data) {
                return $data->category->name;
            })
            ->addColumn('base_category_name', function ($data) {
                return $data->baseCategory->name;
            })
            ->addColumn('subcategory_name', function ($data) {
                if ($data->subcategory == null) {
                    return "Not available";
                } else {
                    return $data->subcategory->name;
                }
            })
            ->addColumn('created_by_name', fn($data) => $data->createdBy->name ?? '')
            ->editColumn('created_at', function ($data) {
                return $data->created_at
                    ? $data->created_at->format('Y-m-d h:i A')
                    : '';
            })

            ->rawColumns(['action', 'data_category_name', 'data_subcategory_name'])
            ->with('total_amount', $totalAmount)
            ->make(true);
    }
}
