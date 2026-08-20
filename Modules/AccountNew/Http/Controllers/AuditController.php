<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\CostInsertAudit;
use App\Models\FundInsertAudit;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AuditController extends Controller
{
    public function auditView()
    {
        return view('account.audit.auditView');
    }

    public function fundAuditView()
    {
        return view('account.audit.fundAudit');
    }


    public function expenseAudit(Request $request)
    {
        $query = CostInsertAudit::with([
            'category',
            'subcategory',
            'createdBy',
            'updatedBy'
        ])->where('soft_delete', 0);

        // Date Filter
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }


        return DataTables::of($query)

            ->addColumn('category_name', fn($data) => $data->category->name ?? '')

            ->addColumn('subcategory_name', fn($data) => $data->subcategory->name ?? '')

            ->editColumn('created_at', function ($data) {
                return $data->created_at
                    ? $data->created_at->format('Y-m-d h:i A')
                    : '';
            })
            ->editColumn('updated_at', function ($data) {
                return $data->updated_at
                    ? $data->updated_at->format('Y-m-d h:i A')
                    : '';
            })

            ->addColumn('created_by_name', fn($data) => $data->createdBy->name ?? '')
            ->addColumn('updated_by_name', fn($data) => $data->updatedBy->name ?? '')
            ->make(true);
    }

    public function fundAudit(Request $request)
    {
        $query = FundInsertAudit::with(['category', 'subcategory'])->where(['soft_delete' => 0])->orderBy('updated_at', 'desc');
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }
        return Datatables::of($query)
            ->addColumn('category_name', function ($data) {
                return $data->category->name;
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
            ->editColumn('updated_at', function ($data) {
                return $data->updated_at
                    ? $data->updated_at->format('Y-m-d h:i A')
                    : '';
            })
            ->addColumn('updated_by_name', fn($data) => $data->updatedBy->name ?? '')

            ->rawColumns(['action', 'data_category_name', 'data_subcategory_name'])
            ->make(true);
    }
}
