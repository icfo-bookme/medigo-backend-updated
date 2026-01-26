@extends('layouts.app')

@section('title', $page_title)
@push('styles')
@endpush
@section('content')

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card-header flex-wrap py-5">
                <form method="get" action="{{route('statement.salary.statement')}}" class="col-md-12 px-0">
                    <div class="row">
                        <div class="col-md-2"></div>
                        <x-form.selectbox labelName="{{__('file.Salary Payment Month')}}" name="payment_month" id="payment_month" col="col-md-4" class="selectpicker">
                            @foreach ($salary_pay as $row)
                                <option value="{{ $row->payment_month}}">{{ date('F-Y', strtotime($row->payment_month))}}</option>
                            @endforeach
                        </x-form.selectbox>
                        <div class="col-md-4">
                            <div style="margin-top:28px;">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                            data-toggle="tooltip" data-theme="dark" title="{{__('file.Reset')}}">
                                        <i class="fas fa-undo-alt"></i></button>

                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="submit"
                                            data-toggle="tooltip" data-theme="dark" title="{{__('file.Search')}}">
                                        <i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </form>
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
                                    <p><span>Employee Salary Statement</span>

                                            <span style="font-size: 16px;float: right;">{{date('F-Y', strtotime($payment_month))}}</span>
                                    </p>
                                    </thead>
                                    <thead class="bg-primary">
                                    <tr>
                                        <th class="text-center" rowspan="5">{{__('SL')}}</th>
                                        <th class="text-center"  rowspan="5">{{__('Employee Name')}}</th>
                                        <th  class="text-center" rowspan="5">{{__('Employee ID')}}</th>
                                        <th class="text-center"  rowspan="5">{{__('Designation')}}</th>
                                        <th  class="text-center" colspan="4">{{__('Total Gross Salary')}}</th>
                                        <th  class="text-center" rowspan="3">{{__('Total')}}</th>
                                        <th  class="text-center" colspan="4">{{__('Total Deductions')}}</th>
                                        <th  class="text-center" rowspan="3">{{__('Total ')}}</th>
                                        <th  class="text-center" rowspan="3">{{__('Payable ')}}</th>
                                        <th  class="text-center" rowspan="3">{{__('Net Payable ')}}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" rowspan="2">{{__('Basic Salary')}}</th>
                                        <th class="text-center" rowspan="2">{{__('Allowance')}}</th>
                                        <th class="text-center" rowspan="2">{{__('Mobil Bill')}}</th>
                                        <th class="text-center" rowspan="2">{{__('Bonus')}}</th>
                                        <th class="text-center" colspan="2">{{__('Absence')}}</th>
                                        <th class="text-center" rowspan="2">{{__('Salary Advance')}}</th>
                                        <th class="text-center" rowspan="2">{{__('Other')}}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" >{{__('Days')}}</th>
                                        <th class="text-center" >{{__('Taka')}}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" >{{__('1')}}</th>
                                        <th class="text-center" >{{__('2')}}</th>
                                        <th class="text-center" >{{__('3')}}</th>
                                        <th class="text-center" >{{__('4')}}</th>
                                        <th class="text-center" >{{__('5')}}</th>
                                        <th class="text-center" >{{__('6')}}</th>
                                        <th class="text-center" >{{__('7')}}</th>
                                        <th class="text-center" >{{__('8')}}</th>
                                        <th class="text-center" >{{__('9')}}</th>
                                        <th class="text-center" >{{__('10')}}</th>
                                        <th class="text-center" >{{__('11')}}</th>
                                        <th class="text-center" >{{__('12')}}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" ></th>
                                        <th class="text-center" ></th>
                                        <th class="text-center" ></th>
                                        <th class="text-center" ></th>
                                        <th class="text-center" >{{__('(1+2+3+4)')}}</th>
                                        <th class="text-center" >{{__('(1/31*5)')}}</th>
                                        <th class="text-center" ></th>
                                        <th class="text-center" ></th>
                                        <th class="text-center" ></th>
                                        <th class="text-center" >{{__('(8+9)')}}</th>
                                        <th class="text-center" >{{__('(5-10)')}}</th>
                                        <th class="text-center" >{{__('(4-9)')}}</th>
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
                                        @endphp
                                    @foreach($salarys as $key=> $row)
                                        <tr>
                                            <td>{{$key+1}}</td>
                                            <td>{{$row->employee->name}}</td>
                                            <td>{{$row->employee->employee_id}}</td>
                                            <td>{{$row->employee->designation->name}}</td>
                                            <td class="text-right">{{$row->salary->basic_salary}}
                                                @php
                                                    $a += $row->salary->basic_salary;
                                                @endphp
                                            </td>
                                            <td class="text-right">{{$row->total_allowance - $row->salary->mobile_allowance}}
                                                @php
                                                    $b += $row->total_allowance - $row->salary->mobile_allowance;
                                                @endphp
                                            </td>
                                            <td class="text-right">{{$row->salary->mobile_allowance}}
                                                @php
                                                    $c += $row->salary->mobile_allowance;
                                                @endphp
                                            </td>
                                            <td class="text-right">{{$row->bonus}}
                                                @php
                                                    $d += $row->bonus;
                                                @endphp
                                            </td>
                                            <td class="text-right">{{$row->salary->basic_salary + $row->total_allowance + $row->bonus}}
                                                @php
                                                    $e += $row->salary->basic_salary + $row->total_allowance + $row->bonus;
                                                @endphp
                                            </td>
                                            <td class="text-right">0</td>
                                            <td class="text-right">0</td>
                                            <td class="text-right">{{$row->salary_advance}}
                                                @php
                                                    $h += $row->salary_advance;
                                                @endphp
                                            </td>
                                            <td class="text-right">{{$row->total_deduction}}
                                                @php
                                                    $i += $row->total_deduction;
                                                @endphp
                                            </td>
                                            <td class="text-right">{{$row->total_deduction + $row->salary_advance}}
                                                @php
                                                    $j += ($row->total_deduction + $row->salary_advance);
                                                @endphp
                                            </td>
                                            <td class="text-right">{{$row->net_salary}}
                                                @php
                                                    $k += $row->net_salary;
                                                @endphp
                                            </td>
                                            <td class="text-right">{{$row->payment_amount}}
                                                @php
                                                    $l += $row->payment_amount;
                                                @endphp
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <tr>
                                            <th colspan="4" class="text-right">Total:</th>
                                            <th class="text-right">{{$a}}</th>
                                            <th class="text-right">{{$b}}</th>
                                            <th class="text-right">{{$c}}</th>
                                            <th class="text-right">{{$d}}</th>
                                            <th class="text-right">{{$e}}</th>
                                            <th class="text-right">0</th>
                                            <th class="text-right">0</th>
                                            <th class="text-right">{{$h}}</th>
                                            <th class="text-right">{{$i}}</th>
                                            <th class="text-right">{{$j}}</th>
                                            <th class="text-right">{{$k}}</th>
                                            <th class="text-right">{{$l}}</th>
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
