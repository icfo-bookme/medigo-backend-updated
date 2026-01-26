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

class RemoveCampaignPricesJob implements ShouldQueue
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
        info('hello from remove ');

        foreach ($this->categoryIds as $categoryId) {
            $products = Product::where('category_id', $categoryId)->select('id')->cursor();
            foreach ($products as $product) {
                ProductUnit::where('product_id', $product->id)
                    ->where('campaign_id', $this->campaignId)
                    ->update([
                        'campaign_id' => null,
                        'campaign_price' => 0.00,
                        'updated_at' => now(),
                    ]);
            }
        }
    }
}
