
<html>
    <head>
        <title>Allowance slip</title>
        <style>
            body {
                background: #f0f0f0;
                width: 100vw;
                height: 100vh;
                display: flex;
                justify-content: center;
                padding: 20px;
                height: 100%;
            }

            @import url('https://fonts.googleapis.com/css?family=Roboto:200,300,400,600,700');

            * {
                font-family: 'Roboto', sans-serif;
                font-size: 12px;
                color: #444;
            }

            #payslip {
                width: calc( 8.5in - 80px );
                height: calc( 11in - 60px );
                background: #fff;
                padding: 30px 40px;
            }

            #title {
                margin-bottom: 20px;
                font-size: 38px;
                font-weight: 600;
            }

            #scope {
                border-top: 1px solid #ccc;
                border-bottom: 1px solid #ccc;
                padding: 7px 0 4px 0;
                display: flex;
                justify-content: space-around;
            }

            #scope > .scope-entry {
                text-align: center;
            }

            #scope > .scope-entry > .value {
                font-size: 14px;
                font-weight: 700;
            }

            .content {
                display: flex;
                border-bottom: 1px solid #ccc;
                height: 880px;
            }

            .content .left-panel {
                border-right: 1px solid #ccc;
                min-width: 50%;
                padding: 20px 16px 0 0;
            }

            .content .right-panel {
                width: 50%;
                padding: 10px 0  0 16px;
            }

            #employee {
                text-align: center;
                margin-bottom: 20px;
            }
            #employee #name {
                font-size: 15px;
                font-weight: 700;
            }

            #employee #email {
                font-size: 11px;
                font-weight: 300;
            }

            .details, .contributions, .ytd, .gross {
                margin-bottom: 20px;
            }

            .details .entry, .contributions .entry, .ytd .entry {
                display: flex;
                justify-content: space-between;
                margin-bottom: 6px;
            }

            .details .entry .value, .contributions .entry .value, .ytd .entry .value {
                font-weight: 700;
                max-width: 130px;
                text-align: right;
            }

            .gross .entry .value {
                font-weight: 700;
                text-align: right;
                font-size: 16px;
            }

            .contributions .title, .ytd .title, .gross .title {
                font-size: 15px;
                font-weight: 700;
                border-bottom: 1px solid #ccc;
                padding-bottom: 4px;
                margin-bottom: 6px;
            }

            .content .right-panel .details {
                width: 100%;
            }

            .content .right-panel .details .entry {
                display: flex;
                padding: 0 10px;
                margin: 6px 0;
            }

            .content .right-panel .details .label {
                font-weight: 700;
                width: 120px;
            }

            .content .right-panel .details .detail {
                font-weight: 600;
                width: 130px;
            }

            .content .right-panel .details .rate {
                font-weight: 400;
                width: 80px;
                font-style: italic;
                letter-spacing: 1px;
            }

            .content .right-panel .details .amount {
                text-align: right;
                font-weight: 700;
                width: 90px;
            }

            .content .right-panel .details .net_pay div, .content .right-panel .details .nti div {
                font-weight: 600;
                font-size: 12px;
            }

            .content .right-panel .details .net_pay, .content .right-panel .details .nti {
                padding: 3px 0 2px 0;
                margin-bottom: 10px;
                background: rgba(0, 0, 0, 0.04);
            }

        </style>
    </head>
</html>
<div id="payslip">
    <a  class="printPage" href="#">Print</a>
    <div id="title" style="text-align: center;">
        Allowance slip
        <div class="entry">
            <div class="label">Employee Name: {{$allowance->employee->name}}</div>
            <div class="label">Employee ID: {{$allowance->employee->employee_id}}</div>
        </div>
    </div>
    <div id="scope">
        <div class="scope-entry">
            <div class="title">Allowance Payment Month</div>
            <div class="value">{{date('F-Y', strtotime($month))}}</div>
        </div>
    </div>
    <div class="content">
        <div class="left-panel">
            <div id="employee">
                <div id="name">
                    Regular Allowance
                </div>

            </div>
            <div class="details">
                <div class="entry">
                    <div class="label">House Rent Allowance</div>
                    <div class="value">
                        {{$allowance->house_rent_allowance ? $allowance->house_rent_allowance : '0.00'}}/-TK
                    </div>
                </div>
                <div class="entry">
                    <div class="label">Medical Allowance</div>
                    <div class="value">
                        {{$allowance->medical_allowance ? $allowance->medical_allowance : '0.00'}}/-TK
                    </div>
                </div>
                <div class="entry">
                    <div class="label">Transport Allowance</div>
                    <div class="value">
                        {{$allowance->transport_allowance ? $allowance->transport_allowance : '0.00'}}/-TK
                    </div>
                </div>
                <div class="entry">
                    <div class="label">Mobile Allowance</div>
                    <div class="value">
                        {{$allowance->mobile_allowance ? $allowance->special_allowance : '0.00'}}/-TK
                    </div>
                </div>
                <div class="entry">
                    <div class="label">Other Allowance</div>
                    <div class="value">
                        {{$allowance->other_allowance ? $allowance->other_allowance : '0.00'}}/-TK
                    </div>
                </div>
                <div class="gross">
                    <div class="title"></div>
                    <div class="entry">
                        <div class="title" style="border: none">Total Regular Allowance</div>
                        <div class="label"></div>
                        <div class="value">{{$allowance->house_rent_allowance + $allowance->medical_allowance + $allowance->transport_allowance + $allowance->mobile_allowance + $allowance->other_allowance}}/-TK</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="left-panel" style="border: none;">
            <div id="employee">
                <div id="name">
                    Special Allowance
                </div>
            </div>
            @php
                $amount = 0;
            @endphp
            @foreach($other_allowance as $row)
            <div class="details">
                <div class="entry">
                    <div class="label" style="padding-left:5%">
                        {{$row->allowances_name}}
                    </div>
                    <div class="value">
                        {{$row->allowances_amount}}/-TK
                        @php
                            $amount += $row->allowances_amount
                        @endphp
                    </div>
                </div>
            </div>
            @endforeach
            <div class="gross">
                <div class="title"></div>
                <div class="entry">
                    <div class="title" style="border: none;padding-left:5%">
                        Total Special Allowance
                    <span style="float: right;font-weight: 700;font-size: 18px;">{{$amount}}/-TK</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script>
    $('a.printPage').click(function(){
        window.print();
        return false;
    });
</script>
