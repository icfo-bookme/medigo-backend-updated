<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateCampaignPricesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $categoryIds;
    protected $campaignId;
    protected $campaign;

    /**
     * Create a new job instance.
     */
    public function __construct(array $categoryIds, $campaignId, $campaign)
    {
        $this->categoryIds = $categoryIds;
        $this->campaignId = $campaignId;
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        info('hello from add ');
         

        foreach ($this->categoryIds as $categoryId) {

            
            ProductUnit::whereIn('product_id', function ($query) use ($categoryId) {
                $query->select('id')
                    ->from('products')
                    ->where('category_id', $categoryId);
            })->select('price', 'product_id', 'id')
              ->chunk(500, function ($productUnitsChunk) {

                info('inside chunk');

                  foreach ($productUnitsChunk as $unit) {
                      $unit->update([
                          'campaign_id' => $this->campaignId,
                          'campaign_price' => $unit->price - ($this->campaign['discount_type'] == 'percentage'
                              ? ($unit->price * $this->campaign['discount_amount']) / 100
                              : $this->campaign['discount_amount']),
                          'modified_by' => optional(auth()->user())->name,
                          'updated_at' => now(),
                      ]);
                  }
              });
        }

        

    }
}
