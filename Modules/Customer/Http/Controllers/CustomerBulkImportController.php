<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Jobs\ProcessCustomerUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Modules\Customer\Entities\Customer;

class CustomerBulkImportController extends BaseController
{
    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    public function bulk_import()
    {
        if (permission('customer-access')) {
            $this->setPageData('Customer Bulk Import', 'Customer Bulk Import', 'fas fa-file-import', [['name' => 'Customer Bulk Import']]);
            return view('customer::customer_bulk_import');
        } else {
            return $this->access_blocked();
        }
    }

    public function download_file()
    {
        $headers = ['warehouse_id', 'name', 'phone', 'email', 'country', 'district', 'city', 'thana', 'area', 'image', 'information', 'optional_information', 'otp', 'password', 'status', 'created_by', 'modified_by'];
        $sampleRow = [1, 'Demo Customer', '01700000000', 'sample@gmail.com', 'Bangladesh', 'District', 'City', 'Thana', 'Area', '', 'Address', '', '', '12345678', 1, 'SuperAdmin', 'SuperAdmin'];

        // Create a CSV string
        $csvData = fopen('php://temp', 'r+');
        fputcsv($csvData, $headers);
        fputcsv($csvData, $sampleRow);
        rewind($csvData);
        $csvContent = stream_get_contents($csvData);
        fclose($csvData);

        // Return the file as a download response
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample_customers.csv"',
        ]);

    }

    public function store(Request $request)
    {
        if ($request->ajax() && permission('customer-add')) {
            // Validate that a file is uploaded
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,excel,xls,xlsx,txt',
            ]);

            // Store the file temporarily and get the path
            $filePath = $request->file('csv_file')->storeAs('temp', 'customer_upload.csv');

            // Dispatch the job to run asynchronously (without sync)
            ProcessCustomerUpload::dispatch(storage_path('app/' . $filePath));

            // Dispatch the job to run asynchronously
//            ProcessCustomerUpload::dispatch(storage_path('app/' . $filePath))->onConnection('sync');

            return response()->json(['status' => 'success', 'message' => 'Bulk import process started. You will be notified once it is completed.']);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function show($id)
    {
        return view('customer::show');
    }
}
