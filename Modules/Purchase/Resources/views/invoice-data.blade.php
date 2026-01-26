<div class="col-md-12 col-lg-12" style="width: 100%;">
    <div id="invoice">
        <div class="invoice overflow-auto">
            <div>
                <table>
                    <tr>
                        <td class="text-center">
                            <h2 class="name m-0" style="text-transform: uppercase;"><b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                            @if(config('settings.contact_no'))
                                <p style="font-weight: normal;margin:0;"><b>Contact No.: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))
                                        <b>Email: </b>{{ config('settings.email') }}
                                    @endif</p>
                            @endif
                            @if(config('settings.address'))
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
                                <div class="text-grey-light"><b>BILLING TO</b></div>
                                @if($purchase->supplier->company_name)
                                    <div class="to">{{ $purchase->supplier->company_name }}</div>
                                @endif
                                <div class="to">{{ $purchase->supplier->name }}</div>
                                <div class="phone">{{ $purchase->supplier->mobile }}</div>
                                @if($purchase->supplier->email)
                                    <div class="email">{{ $purchase->supplier->email }}</div>
                                @endif
                                @if($purchase->supplier->address)
                                    <div class="address">{{ $purchase->supplier->address }}</div>
                                @endif
                            </div>
                        </td>
                        <td width="50%" class="text-right">
                            <h4 class="name m-0">{{ $purchase->memo_no }}</h4>
                            <div class="m-0 date"><b>Date:</b>{{ date('d-M-Y',strtotime($purchase->purchase_date)) }}</div>
                            <div class="m-0 date"><b>Purchase Status: </b>{{ $purchase->purchase_status ? PURCHASE_STATUS[$purchase->purchase_status] : 'N/A' }}</div>
                            <div class="m-0 date"><b>Payment Status: </b>{{ PAYMENT_STATUS[$purchase->payment_status] }}</div>
                        </td>
                    </tr>
                </table>
                <table border="0" cellspacing="0" cellpadding="0">
                    <thead>
                    <tr>
                        <th class="text-center">SL</th>
                        <th class="text-left">DESCRIPTION</th>
                        <th class="text-center">QUANTITY</th>
                        <th class="text-right">PRICE</th>
                        <th class="text-right">SUBTOTAL</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (!$purchase->purchase_products->isEmpty())
                        @foreach ($purchase->purchase_products as $key => $item)
                            @php
                                $unit_name = '';
                                if($item->purchase_unit_id)
                                {
                                    $unit_name = DB::table('units')->where('id',$item->purchase_unit_id)->value('unit_name');
                                }
                            @endphp
                            <tr>
                                <td class="text-center no">{{ $key+1 }}</td>
                                <td>
                                    {{ $item->product_variant_id ? $item->product_variant->product->name.' - ('.$item->product_variant->item_code.')' : ''}}
                                </td>
                                <td class="text-center qty">{{ $item->qty}} ({{$unit_name}})</td>
                                <td class="text-right price">{{ number_format($item->net_unit_cost,2) }}</td>
                                <td class="text-right total">
                                    @if (config('settings.currency_position') == 2)
                                        {{ number_format($item->total,2) }} {{ config('settings.currency_symbol') }}
                                    @else
                                        {{ config('settings.currency_symbol') }} {{ number_format($item->total,2) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2" class="text-right">TOTAL</td>
                        <td class="text-right">
                            @if (config('settings.currency_position') == 2)
                                {{ number_format($purchase->total_cost,2) }} {{ config('settings.currency_symbol') }}
                            @else
                                {{ config('settings.currency_symbol') }} {{ number_format($purchase->total_cost,2) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left"></td>
                        <td colspan="2" class="text-right">DISCOUNT</td>
                        <td class="text-right">
                            @if (config('settings.currency_position') == 2)
                                {{ number_format($purchase->order_discount,2) }} {{ config('settings.currency_symbol') }}
                            @else
                                {{ config('settings.currency_symbol') }} {{ number_format($purchase->order_discount,2) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left"></td>
                        <td colspan="2" class="text-right">TAX {{ $purchase->order_tax_rate }}%</td>
                        <td class="text-right">
                            @if (config('settings.currency_position') == 2)
                                {{ number_format($purchase->order_tax,2) }} {{ config('settings.currency_symbol') }}
                            @else
                                {{ config('settings.currency_symbol') }} {{ number_format($purchase->order_tax,2) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2" class="text-right">SHIPPING COST</td>
                        <td class="text-right">
                            @if (config('settings.currency_position') == 2)
                                {{ number_format($purchase->shipping_cost,2) }} {{ config('settings.currency_symbol') }}
                            @else
                                {{ config('settings.currency_symbol') }} {{ number_format($purchase->shipping_cost,2) }}
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2" class="text-right">GRAND TOTAL</td>
                        <td class="text-right">
                            @if (config('settings.currency_position') == 2)
                                {{ number_format($purchase->grand_total,2) }} {{ config('settings.currency_symbol') }}
                            @else
                                {{ config('settings.currency_symbol') }} {{ number_format($purchase->grand_total,2) }}
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2" class="text-right">PAID AMOUNT</td>
                        <td class="text-right">
                            @if (config('settings.currency_position') == 2)
                                {{ number_format($purchase->paid_amount,2) }} {{ config('settings.currency_symbol') }}
                            @else
                                {{ config('settings.currency_symbol') }} {{ number_format($purchase->paid_amount,2) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2" class="text-right">DUE AMOUNT</td>
                        <td class="text-right">
                            @if (config('settings.currency_position') == 2)
                                {{ number_format(($purchase->grand_total - $purchase->paid_amount),2) }} {{ config('settings.currency_symbol') }}
                            @else
                                {{ config('settings.currency_symbol') }} {{ number_format(($purchase->grand_total - $purchase->paid_amount),2) }}
                            @endif
                        </td>
                    </tr>
                    </tfoot>
                </table>
                <table>
                    <tr>
                        <td>
                            <div class="thanks"><h4>Thank you!</h4></div>
                            <div class="notices">
                                <div>Note:</div>
                                <div class="notice">{{ $purchase->note }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
                <table style="width: 100%;">
                    <tr>
                        <td class="text-center">
                            <div class="font-size-10" style="width:250px;float:left;">
                                <p style="margin:0;padding:0;"><b class="text-uppercase">{{ $purchase->created_by }}</b>
                                    <br> {{ date('d-M-Y h:i:s A',strtotime($purchase->created_at)) }}</p>
                                <p class="dashed-border"></p>
                                <p style="margin:0;padding:0;">Received By</p>
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="font-size-10" style="width:250px;float:right;">
                                <p style="margin:0;padding:0;"><b class="text-uppercase"></b></p>
                                <p class="dashed-border"></p>
                                <p style="margin:0;padding:0;">Authorized By</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
