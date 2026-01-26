<div class="row">
    @foreach($products as $key => $product)
        <div class="col-md-3 mb-4">
            <div class="card" style="height: 200px">
                <div class="card-body" style="padding: 5px 5px; overflow: hidden;">
                    <div class="col-md-12">
                        <div class="form-check">
                            @php
                                $campaign_id = request()->campaign_id;
                                // Check if the product exists in the current campaign
                                $campaign_product = \Modules\Campaign\Entities\CampaignProduct::where('campaign_id', $campaign_id)
                                    ->whereJsonContains('product_ids', $product->id)->first();
                            @endphp
                            <input class="form-check-input" type="checkbox" name="products[{{ $key + 1 }}][product_check]"
                                   id="product_check_{{ $key + 1 }}" value="1" {{ $campaign_product ? 'checked' : '' }} disabled>
                        </div>
                        <input name="products[{{ $key + 1 }}][product_id]" id="product_id_{{ $key + 1 }}" type="hidden" value="{{ $product->id }}">

                        <p style="text-align: center;margin: auto;">
                            <img src="{{ asset('storage/' . PRODUCT_IMAGE_PATH . $product->image) }}" alt="" style="width:95px;height: 100px;"/>
                        </p>
                    </div>
                    <p style="text-align: center;margin: auto;font-size: 12px;color: #034d97;font-weight: 600;">
                        {{ $product->name }}
                    </p>
                    <p style="text-align: center; font-size: 12px; color: black; font-weight: 500;">
                        Sale Price: {{ $product->productUnits[0]->price ?? 'N/A' }}<br>
                        Campaign Price: {{ $product->productUnits[0]->campaign_price ?? 'N/A' }}<br>
                        Discount Amount: {{ $product->productUnits[0]->campaign->discount_amount ?? 'N/A' }}
                    </p>

                </div>
            </div>
        </div>
    @endforeach
</div>
