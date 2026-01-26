<div class="row">
    @foreach($products as $key => $product)
        <div class="col-md-3 mb-4">
            <div class="card" style="height: 215px">
                <div class="card-body" style="padding: 5px 5px; overflow: hidden;">
                    <div class="col-md-12 cursor-pointer" onclick="toggleCheckbox('{{ $key + 1 }}')">
                        <div class="form-check">
                            @php
                                $campaign_id = request()->campaign_id;
                                // Check if the product exists in the current campaign
                                $campaign_product = \Modules\Campaign\Entities\CampaignProduct::where('campaign_id', $campaign_id)
                                    ->whereJsonContains('product_ids', $product->id)->first();

                                // Check if the product is assigned to another campaign
                                $campaign_check = \Modules\Product\Entities\ProductUnit::with('campaign')
                                    ->where('product_id', $product->id)
                                    ->whereNotNull('campaign_id')
                                    ->where('campaign_id', '!=', $campaign_id)
                                    ->first();
                                if ($campaign_check){
                                    $newCampaign = \Modules\Campaign\Entities\Campaign::find($campaign_id);
                                    if ($newCampaign->discount_type == 'percentage') {
                                        $newPrice = $campaign_check->price - ($campaign_check->price * $newCampaign->discount_amount / 100);
                                    } else {
                                        $newPrice = $campaign_check->price - $newCampaign->discount_amount;
                                    }
                                }
                            @endphp
                                <!-- Checkbox to select product for campaign -->
                            <input class="form-check-input" type="checkbox" name="products[{{ $key + 1 }}][product_check]"
                                   id="product_check_{{ $key + 1 }}" value="1" {{ $campaign_product ? 'checked' : '' }}>
                        </div>
                        <input name="products[{{ $key + 1 }}][product_id]" id="product_id_{{ $key + 1 }}" type="hidden" value="{{ $product->id }}">

                        <p style="text-align: center;margin: auto;">
                            <img src="{{ asset('storage/' . PRODUCT_IMAGE_PATH . $product->image) }}" alt="" style="width:95px;height: 100px;"/>
                        </p>
                    </div>

                    <p style="text-align: center;margin: auto;font-size: 12px;color: #034d97;font-weight: 600;">
                        {{ $product->name }}
                    </p>

                    @if ($campaign_check)
                        <p style="text-align: center; font-size: 12px; color: red; font-weight: 500;" id="campaign-check-section">
                            Already Assigned to "{{ $campaign_check->campaign->name }}"<br>
                            Existing Price: {{ $campaign_check->campaign_price ?? 'N/A' }}<br>
                            New Price: {{ $newPrice ?? 'N/A' }}<br>
                            @if($campaign_product)
                                <button type="button" class="btn btn-sm btn-secondary rounded" onclick="toggleCheckbox('{{ $key + 1 }}')"><i class="fas fa-trash"></i>Remove</button>
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
