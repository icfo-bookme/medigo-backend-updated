<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Customer\Entities\Customer;
use Modules\Setting\Entities\Warehouse;

class ProcessCustomerUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $file = fopen($this->filePath, 'r');
            $isHeader = true;
            $customers = [];

            // Load valid warehouse IDs to avoid foreign key constraint violations
            $validWarehouseIds = Warehouse::pluck('id')->toArray();

            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }

                // Skip if warehouse_id is invalid
                if (!in_array($data[0], $validWarehouseIds)) {
                    Log::warning("Skipping customer due to invalid warehouse_id: " . $data[0]);
                    continue;
                }

                // Skip if email is already in the database
                if (!empty($data[3]) && Customer::where('email', $data[3])->exists()) {
                    Log::warning("Skipping customer with duplicate email: " . $data[3]);
                    continue;
                }

                // Skip if phone is already in the database
                if (Customer::where('phone', $data[2])->exists()) {
                    Log::warning("Skipping customer with duplicate phone: " . $data[2]);
                    continue;
                }

                $customers[] = [
                    'warehouse_id' => $data[0] ?? 1,
                    'name' => $data[1],
                    'phone' => $data[2],
                    'email' => $data[3] ?? null,
                    'country' => $data[4] ?? null,
                    'district' => $data[5] ?? null,
                    'city' => $data[6] ?? null,
                    'thana' => $data[7] ?? null,
                    'area' => $data[8] ?? null,
                    'image' => $data[9] ?? null,
                    'information' => $data[10] ?? null,
                    'optional_information' => $data[11] ?? null,
                    'otp' => $data[12] ?? null,
                    'password' => bcrypt($data[13]),
                    'status' => $data[14],
                    'created_by' => $data[15] ?? Auth::user()->name(),
                    'modified_by' => $data[16] ?? Auth::user()->name(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            fclose($file);

            // Insert customers in chunks to optimize performance
            foreach (array_chunk($customers, 100) as $chunk) {
                Customer::insert($chunk);
                Log::info("Inserted " . count($chunk) . " customers.");
            }

            DB::commit();
            Log::info("Customer data successfully imported.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error importing customers: " . $e->getMessage());
        } finally {
            // Delete the temporary file
            Storage::delete('temp/customer_upload.csv');
        }
    }
}
