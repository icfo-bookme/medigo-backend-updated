<div class="row">
    @foreach($categories as $key => $category)
        <div class="col-md-3 mb-4">
            <div class="card" style="height: 180px">
                <div class="card-body" style="padding: 5px 5px; overflow: hidden;">
                    <div class="col-md-12 cursor-pointer" onclick="toggleCheckbox('{{ $key + 1 }}')">
                        <div class="form-check">
                            @php
                                $campaign_id = request()->campaign_id;
                                $campaign_category = \Modules\Campaign\Entities\CampaignCategory::where('campaign_id', $campaign_id)
                                    ->whereJsonContains('category_ids', $category->id)->first();
                                $campaign_check = \Modules\Campaign\Entities\CampaignCategory::where('campaign_id', '!=', $campaign_id)
                                    ->whereJsonContains('category_ids', $category->id)->first();
                            @endphp
                            <input class="form-check-input" type="checkbox" name="categories[{{ $key + 1 }}][category_check]"
                                   id="category_check_{{ $key + 1 }}" value="1" {{ $campaign_category ? 'checked' : '' }}>
                        </div>
                        <input name="categories[{{ $key + 1 }}][category_id]" id="category_id_{{ $key + 1 }}" type="hidden" value="{{ $category->id }}">

                        <p style="text-align: center;margin: auto;">
                            <img src="{{ asset('storage/' . PRODUCT_IMAGE_PATH . $category->image) }}" alt="" style="width:95px;height: 100px;"/>
                        </p>
                    </div>

                    <p style="text-align: center;margin: auto;font-size: 12px;color: #034d97;font-weight: 600;">
                        {{ $category->name }}
                    </p>
                    @if($campaign_check)
                        <p style="text-align: center; font-size: 12px; color: red; font-weight: 500;" id="campaign-check-section">
                            Already Assigned to "{{ $campaign_check->campaign->name }}"<br>
                            Discount: @if($campaign_check->campaign->discount_type == 'percentage')
                                {{ $campaign_check->campaign->discount_amount }} %
                            @else
                                {{ $campaign_check->discount_amount }} Tk
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
