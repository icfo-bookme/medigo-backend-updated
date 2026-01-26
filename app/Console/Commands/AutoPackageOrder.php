<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\OrderController;
use App\Jobs\AutoOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\ProductUnit;
use Modules\Sale\Entities\OrderPackage;
use Modules\Sale\Entities\Sale;

class AutoPackageOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-order:package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $packages = OrderPackage::whereDate('start_date', '<=',   now())->where('status',1)->latest()->get();


        foreach ($packages as $pack){
            $start_date =$pack->start_date;
            $days_after = $pack->auto_order_after_days;
            $day_counter = $pack->day_counter;
            if($day_counter < $days_after ){
                $pack->increment('day_counter');
            }else{
                AutoOrder::dispatch($pack);
                $pack->update(['day_counter'=> 0]);
            }
        }
    }
}
