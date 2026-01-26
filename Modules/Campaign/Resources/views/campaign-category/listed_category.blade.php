<div class="row">
    @foreach($categories as $key => $category)
        <div class="col-md-3 mb-4">
            <div class="card" style="height: 150px">
                <div class="card-body" style="padding: 5px 5px; overflow: hidden;">
                    <div class="col-md-12">
                        <div class="form-check">
                            @php
                                $campaign_id = request()->campaign_id;
                                $campaign_category = \Modules\Campaign\Entities\CampaignCategory::where('campaign_id', $campaign_id)
                                    ->whereJsonContains('category_ids', $category->id)->first();
                            @endphp
                            <input class="form-check-input" type="checkbox" name="categories[{{ $key + 1 }}][category_check]"
                                   id="category_check_{{ $key + 1 }}" value="1" {{ $campaign_category ? 'checked' : '' }} disabled>
                        </div>
                        <input name="categories[{{ $key + 1 }}][category_id]" id="category_id_{{ $key + 1 }}" type="hidden" value="{{ $category->id }}">

                        <p style="text-align: center;margin: auto;">
                            <img src="{{ asset('storage/' . PRODUCT_IMAGE_PATH . $category->image) }}" alt="" style="width:95px;height: 100px;"/>
                        </p>
                    </div>

                    <p style="text-align: center;margin: auto;font-size: 12px;color: #034d97;font-weight: 600;">
                        {{ $category->name }}
                    </p>
                </div>
            </div>
        </div>
    @endforeach
</div>
