    <div class="card card-custom card-border">
        <div class="card-body px-5">
            <div class="col-md-12 px-0">
                <div class="row">
                    <div class="form-group col-md-12 px-1">
                        <select class="form-control selectpicker" name="prescription_order_id" id="prescription_order_id" onchange="load_products()" data-live-search="true">
                            <option value="0">Select Prescription Order</option>
                            @if (!$items->isEmpty())
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}" @if($item->id == $prescription_order_id) selected @endif>
                                        {{ $item->phone }} - {{ $item->name  }} - {{ $item->address }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="row pt-5" id="product-section">
                    @include('sale::prescription-order.pos-product-list')
                </div>
            </div>
        </div>
    </div>
