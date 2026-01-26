<?php

namespace App\Services;


use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\Sale;

class SaleInvoiceContainer
{
    public function invoice_code_provider()
    {
        DB::beginTransaction(); // Start a DB transaction for concurrency safety

        try {
            // Lock the sale table to prevent other requests from modifying it simultaneously
            $latestSale = Sale::orderBy('date_wise_serial', 'desc')->lockForUpdate()->first();

            // Initialize or increment the date-wise serial as needed
            $currentDate = date('ymd');
            if (!$latestSale || substr($latestSale->date_wise_serial, 0, 6) !== $currentDate) {
                $newSerial = (int)($currentDate . '000');
            } else {
                $newSerial = (int)$latestSale->date_wise_serial + 1;
            }

            DB::commit(); // Commit the transaction
            return $newSerial;

        } catch (\Exception $e) {
            DB::rollBack(); // Roll back if there’s an error
            throw $e; // Rethrow the exception to be handled by the caller
        }
    }

}
