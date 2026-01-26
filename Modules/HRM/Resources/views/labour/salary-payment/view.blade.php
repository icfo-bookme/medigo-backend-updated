@extends('layouts.app')

@section('title', $page_title)
@push('styles')
@endpush
@section('content')

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom" style="margin-top: -24px;border-top: 4px solid #00c0ef;">
                <div class="card-body" style="border: 2px solid #eaf2f2;">
                    <!--begin: Datatable-->
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="modal-footer">
                                    <a href="{{route('salaryPayment')}}"><button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">{{__('file.BACK')}}</button></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--begin::Card-->
            <div class="card card-custom">

                <div class="card-body">
                    <!--begin: Datatable-->
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                @if(!empty($salarys))
                                    <table id="dataTable" class="table table-bordered table-hover">
                                        <thead>
                                        <p style="text-align: center;font-weight: 700;"><span>FAKIRHAT FEED MILLS LIMITED</span><br><span>Barasia,Fakirhat,Bagerhat</span> </p>
                                        <p><span>Labour Salary Statement</span>

                                            <span style="font-size: 16px;float: right;">{{date('F-Y', strtotime($payment_month))}}</span>
                                        </p>
                                        </thead>
                                        <thead class="bg-primary">
                                        <tr>
                                            <th class="text-center" rowspan="5">{{__('SL')}}</th>
                                            <th class="text-center" rowspan="5">{{__('Labour Name')}}</th>
                                            <th class="text-center" rowspan="5">{{__('Labour ID')}}</th>
                                            <th class="text-center" colspan="7">{{__('Total Gross Salary')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Total')}}</th>
                                            <th class="text-center" colspan="4">{{__('Total Deductions')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Total')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Net Payable')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Bonus')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Payment')}}</th>
                                        </tr>
                                        <tr>
                                            <th rowspan="2">Basic Wages</th>
                                            <th rowspan="2">Daily Wages</th>
                                            <th rowspan="2">Wages Per Hour</th>
                                            <th rowspan="2">Working Days</th>
                                            <th colspan="2">Overtime</th>
                                            <th rowspan="2">Others</th>
                                            <th colspan="2">Absence</th>
                                            <th rowspan="2">Wages Advance</th>
                                            <th rowspan="2">Others</th>
                                        </tr>
                                        <tr>
                                            <th>Hour</th>
                                            <th>Taka</th>
                                            <th>Hour</th>
                                            <th>Taka</th>
                                        </tr>
                                        <tr>
                                            <th>1</th>
                                            <th>2</th>
                                            <th>3</th>
                                            <th>4</th>
                                            <th>5</th>
                                            <th>6</th>
                                            <th>7</th>
                                            <th>8</th>
                                            <th>9</th>
                                            <th>10</th>
                                            <th>11</th>
                                            <th>12</th>
                                            <th>13</th>
                                            <th>14</th>
                                            <th>15</th>
                                            <th>16</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th>(1/{{$total_days}})</th>
                                            <th>(2/8)</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $a =0;
                                            $b =0;
                                            $c =0;
                                            $d =0;
                                            $e =0;
                                            $f =0;
                                            $g =0;
                                            $h =0;
                                            $i =0;
                                            $j =0;
                                            $k =0;
                                            $l =0;
                                            $m =0;
                                            $n =0;
                                            $o =0;
                                            $p =0;
                                        @endphp
                                        @foreach($salarys as $key=>$row)
                                            <tr>
                                                <input type="hidden" name="labour_id[]" value="">
                                                <input type="hidden" name="basic_salary[]" value="">

                                                <input type="hidden" name="total_working_days[]" value="">
                                                <input type="hidden" name="avg_overtime_hour[]" value="">
                                                <input type="hidden" name="overtime_amount[]" value="">

                                                <input type="hidden" name="total_gross_salary[]" value="">
                                                <input type="hidden" name="avg_absence_hour[]" value="">
                                                <input type="hidden" name="absence_amount[]" value="">

                                                <input type="hidden" name="salary_advance[]" value="">
                                                <input type="hidden" name="other_deduction[]" value="">
                                                <input type="hidden" name="total_deduction[]" value="">
                                                <input type="hidden" name="net_payable[]" value="">
                                                <input type="hidden" name="bonus[]" value="">
                                                <input type="hidden" name="payable_salary[]" value="">
                                                <input type="hidden" name="payment_month[]" value="">



                                                <td>{{$key+1}}</td>
                                                <td>{{$row->labour->name}}</td>
                                                <td>{{$row->labour->labour_id}}</td>
                                                <td class="text-right">{{$row->basic_salary}}/-TK
                                                    @php
                                                        $a += $row->basic_salary;
                                                    @endphp
                                                </td>
                                                <td class="text-right">{{ number_format($row->basic_salary / $total_days,2)}}/-TK</td>
                                                <td class="text-right">{{ number_format(($row->basic_salary / $total_days)/8,2)}}/-TK</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-right">{{$row->others_allowance}}/-TK
                                                    @php
                                                        $g += $row->others_allowance;
                                                    @endphp
                                                </td>
                                                <td class="text-right">{{$row->total_gross_salary}}/-TK
                                                    @php
                                                        $h += $row->total_gross_salary;
                                                    @endphp
                                                </td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-right">{{$row->salary_advance}}/-TK
                                                    @php
                                                        $k += $row->salary_advance;
                                                    @endphp
                                                </td>
                                                <td class="text-right">{{$row->other_deduction}}/-TK
                                                    @php
                                                        $l += $row->other_deduction;
                                                    @endphp
                                                </td>
                                                <td class="text-right">{{$row->total_deduction}}/-TK
                                                    @php
                                                        $m += $row->total_deduction;
                                                    @endphp
                                                </td>
                                                <td class="text-right">{{$row->net_payable}}/-TK
                                                    @php
                                                        $n += $row->net_payable;
                                                    @endphp
                                                </td>
                                                <td class="text-right">{{$row->bonus}}/-TK
                                                    @php
                                                        $o += $row->bonus;
                                                    @endphp
                                                </td>
                                                <td class="text-right">{{$row->payable_salary}}/-TK
                                                    @php
                                                        $p += $row->payable_salary;
                                                    @endphp
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                            <tr class="bg-primary">
                                                <th colspan="3" class="text-right">Total:</th>
                                                <th class="text-right">{{$a}}/-TK</th>
                                                <th class="text-right" colspan="6">{{$g}}/-TK</th>
                                                <th class="text-right">{{$h}}/-TK</th>
                                                <th class="text-right" colspan="3">{{$k}}/-TK</th>
                                                <th class="text-right">{{$l}}/-TK</th>
                                                <th class="text-right">{{$m}}/-TK</th>
                                                <th class="text-right">{{$n}}/-TK</th>
                                                <th class="text-right">{{$o}}/-TK</th>
                                                <th class="text-right">{{$p}}/-TK</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
        </div>
    </div>
@endsection

@push('scripts')

@endpush
