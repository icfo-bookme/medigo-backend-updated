<div class="col-md-12 col-lg-12" style="width: 100%;">
    <div id="invoice">
        <div class="invoice overflow-auto">
            <div>
                <table>
                    <tr>
                        <td class="text-center">
                            <h2 class="name m-0" style="text-transform: uppercase;">
                                <b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                            @if (config('settings.contact_no'))
                                <p style="font-weight: normal;margin:0;"><b>Contact No.:
                                    </b>{{ config('settings.contact_no') }}, @if (config('settings.email'))
                                        <b>Email: </b>{{ config('settings.email') }}
                                    @endif
                                </p>
                            @endif
                            @if (config('settings.address'))
                                <p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>
                            @endif
                            <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
                        </td>
                    </tr>
                </table>
                <div style="width: 100%;height:3px;border-top:1px solid #036;border-bottom:1px solid #036;"></div>
                <table>
                    <tr>
                        <td width="50%">
                            <div class="invoice-to">
                                <div class="text-grey-light"><b>Billed To</b></div>
                                <div class="to"><b>Name :
                                    </b><span>{{ $sale->customer_id ? $sale->customer->name : $sale->name }}</span>
                                </div>
                                @if ($sale->order_type == 1)
                                    <div class="phone"><b>Phone :
                                        </b><span>{{ $sale->customer ? $sale->customer->phone : $sale->phone }}</span></div>
                                @else
                                    <div class="phone"><b>Phone :
                                        </b><span>{{ $sale->customer ? $sale->customer->phone : $sale->phone }}</span></div>
                                @endif

                                <div class="address"><b>Address :
                                    </b><span>{{ $sale->customer ? $sale->customer->information : $sale->information }}</span>
                                    <span>
                                        <br>{{ $sale->customer ? $sale->customer->optional_information : $sale->optional_information }}</span>
                                </div>

                            </div>
                        </td>
                        <td width="50%" class="text-right">
                            <h4 class="name m-0">{{ $sale->invoice_no }}</h4>
                            <div class="m-0 date"><b>Date:</b>{{ date('d-M-Y', strtotime($sale->sale_date)) }}</div>
                            <div class="m-0 date"><b>Status:</b><button type="button"
                                    class="btn btn-primary">{{ VOUCHER_STATUS[$sale->delivery_status] }}</button></div>
                        </td>
                    </tr>
                </table>
                <table border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th class="text-center">SL</th>
                            <th class="text-left">PRODUCT</th>
                            <th class="text-center">UNIT</th>
                            <th class="text-center">QUANTITY</th>
                            <th class="text-right">PRICE</th>
                            <th class="text-right">DISCOUNT (%)</th>
                            <th class="text-right">DISCOUNT AMOUNT</th>
                            <th class="text-right">SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$sale->products->isEmpty())
                            @foreach ($sale->products as $key => $item)
                                @php
                                    $unit_name = DB::table('units')
                                        ->where('id', $item->pivot->sale_unit_id)
                                        ->value('unit_name');
                                @endphp
                                <tr>
                                    <td class="text-center no">{{ $key + 1 }}</td>
                                    <td class="text-left">
                                        {{ $item->name ? $item->name : '' }}<br><b>{{ $item->code }}</b></td>
                                    <td class="text-center qty">{{ $unit_name }}</td>
                                    <td class="text-center qty">{{ $item->pivot->qty }}</td>
                                    <td class="text-right price">{{ number_format($item->pivot->net_unit_price, 2) }}
                                    </td>
                                    <td class="text-right discount">{{ number_format($item->pivot->discount, 2) }}</td>
                                    <td class="text-right tax">{{ number_format($item->pivot->tax, 2) }}</td>
                                    <td class="text-right total"> {{ number_format($item->pivot->total, 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5"></td>
                            <td colspan="2" class="text-right">NET TOTAL</td>
                            <td class="text-right">
                                @if (config('settings.currency_position') == 2)
                                    {{ number_format($sale->net_total, 2, '.', ',') }}
                                    {{ config('settings.currency_symbol') }}
                                @else
                                    {{ config('settings.currency_symbol') }}
                                    {{ number_format($sale->net_total, 2, '.', ',') }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-left"></td>
                            <td colspan="2" class="text-right">TAX {{ $sale->order_tax_rate }}%</td>
                            <td class="text-right">
                                @if (config('settings.currency_position') == 2)
                                    {{ number_format($sale->order_tax, 2) }} {{ config('settings.currency_symbol') }}
                                @else
                                    {{ config('settings.currency_symbol') }} {{ number_format($sale->order_tax, 2) }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-left"></td>
                            <td colspan="2" class="text-right">DISCOUNT @if ($sale->order_discount_per == 2)
                                    %
                                @else
                                    TK
                                @endif
                            </td>
                            <td class="text-right">
                                @if (config('settings.currency_position') == 2)
                                    {{ number_format($sale->order_discount, 2) }} @if ($sale->order_discount_per == 1)
                                        {{ config('settings.currency_symbol') }}
                                    @endif
                                @else
                                    @if ($sale->order_discount_per == 1)
                                        {{ config('settings.currency_symbol') }}
                                    @endif{{ number_format($sale->order_discount, 2) }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5"></td>
                            <td colspan="2" class="text-right">SHIPPING COST</td>
                            <td class="text-right">
                                @if (config('settings.currency_position') == 2)
                                    {{ number_format($sale->shipping_cost, 2, '.', ',') }}
                                    {{ config('settings.currency_symbol') }}
                                @else
                                    {{ config('settings.currency_symbol') }}
                                    {{ number_format($sale->shipping_cost, 2, '.', ',') }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-left"></td>
                            <td colspan="2" class="text-right">ADJUSTMENT @if ($sale->adjustment_per == 2)
                                    (-)
                                @else
                                    (+)
                                @endif
                            </td>
                            <td class="text-right">
                                @if (config('settings.currency_position') == 2)
                                    {{ number_format($sale->adjustment, 2) }} {{ config('settings.currency_symbol') }}
                                @else
                                    {{ config('settings.currency_symbol') }} {{ number_format($sale->adjustment, 2) }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5" class="text-left"></td>
                            <td colspan="2" class="text-right">COUPON ({{ $sale->coupon->name ?? '' }})</td>
                            <td class="text-right">
                                @if (!empty($sale->coupon))
                                    @if ($sale->coupon->type == 1)
                                        {{ $sale->coupon->value }} /-Tk
                                    @elseif($sale->coupon->type == 2)
                                        {{ $sale->coupon->value }} %
                                    @endif
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5"></td>
                            <td colspan="2" class="text-right">ORDER SOURCE</td>
                            <td class="text-right">
                                {{ $sale->order_source_id ? ORDER_SOURCE[$sale->order_source_id] : '' }}
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5"></td>
                            <td colspan="2" class="text-right">GRAND TOTAL</td>
                            <td class="text-right">
                                @if (config('settings.currency_position') == 2)
                                    {{ number_format($sale->grand_total, 2, '.', ',') }}
                                    {{ config('settings.currency_symbol') }}
                                @else
                                    {{ config('settings.currency_symbol') }}
                                    {{ number_format($sale->grand_total, 2, '.', ',') }}
                                @endif
                            </td>
                        </tr>

                        {{--                                        <tr> --}}
                        {{--                                            <td colspan="5"></td> --}}
                        {{--                                            <td colspan="2"  class="text-right">PAID AMOUNT</td> --}}
                        {{--                                            <td class="text-right"> --}}
                        {{--                                                @if (config('settings.currency_position') == 2) --}}
                        {{--                                                    {{ number_format($sale->paid_amount,2,'.',',') }} {{ config('settings.currency_symbol') }} --}}
                        {{--                                                @else --}}
                        {{--                                                    {{ config('settings.currency_symbol') }} {{ number_format($sale->paid_amount,2,'.',',') }} --}}
                        {{--                                                @endif --}}
                        {{--                                            </td> --}}
                        {{--                                        </tr> --}}
                        {{--                                        <tr> --}}
                        {{--                                            <td colspan="5"></td> --}}
                        {{--                                            <td colspan="2"  class="text-right">CHANGE AMOUNT</td> --}}
                        {{--                                            <td class="text-right"> --}}
                        {{--                                                @if (config('settings.currency_position') == 2) --}}
                        {{--                                                    {{ number_format($sale->change,2,'.',',') }} {{ config('settings.currency_symbol') }} --}}
                        {{--                                                @else --}}
                        {{--                                                    {{ config('settings.currency_symbol') }} {{ number_format($sale->change,2,'.',',') }} --}}
                        {{--                                                @endif --}}
                        {{--                                            </td> --}}
                        {{--                                        </tr> --}}
                    </tfoot>
                </table>

                <table style="width: 100%;">
                    <tr>
                        <td class="text-center">
                            <div class="font-size-10" style="width:250px;float:left;">
                                <p style="margin:0;padding:0;"></p>
                                <p class="dashed-border"></p>
                                <p style="margin:0;padding:0;">Received By</p>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="font-size-10" style="width:250px;float:right;">
                                <p style="margin:35px 0 0 0;padding:0;"><b class="text-uppercase">
                                        {{ $sale->created_by ?? 'Online User' }}
                                    </b><br> {{ date('d-M-Y h:i:s A', strtotime($sale->created_at)) }}</p>
                                <p class="dashed-border"></p>
                                <p style="margin:0;padding:0;">Generated By</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
