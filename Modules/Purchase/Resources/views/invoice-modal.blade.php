@push('styles')
    <style>
        body,html {
            background: #fff !important;
            -webkit-print-color-adjust: exact !important;
        }
        .invoice {
            /* position: relative; */
            background: #fff !important;
            /* min-height: 680px; */
        }
        .invoice header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #036;
        }
        .invoice .company-details {
            text-align: right
        }
        .invoice .company-details .name {
            margin-top: 0;
            margin-bottom: 0;
        }
        .invoice .contacts {
            margin-bottom: 20px;
        }
        .invoice .invoice-to {
            text-align: left;
        }
        .invoice .invoice-to .to {
            margin-top: 0;
            margin-bottom: 0;
        }
        .invoice .invoice-details {
            text-align: right;
        }
        .invoice .invoice-details .invoice-id {
            margin-top: 0;
            color: #036;
        }
        .invoice main {
            padding-bottom: 50px
        }
        .invoice main .thanks {
            margin-top: -100px;
            font-size: 2em;
            margin-bottom: 50px;
        }
        .invoice main .notices {
            padding-left: 6px;
            border-left: 6px solid #036;
        }
        .invoice table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }
        .invoice table th {
            background: white;
            color: black;
            padding: 15px;
            border : 2px solid black;
        }
        .invoice table td {
            padding: 15px;
            border-bottom: 1px solid #fff
        }
        .invoice table th {
            white-space: nowrap;
        }
        .invoice table td h3 {
            margin: 0;
            color: #036;
        }
        .invoice table .qty {
            text-align: center;
        }
        .invoice table .price,
        .invoice table .discount,
        .invoice table .tax,
        .invoice table .total {
            text-align: right;
        }
        .invoice table .no {
            color: black;
            background: white;
            border : 2px solid black !important;
        }
        .invoice table .total {
            background: white;
            color: black;
            border : 2px solid black !important;
        }
        .invoice table tbody tr:last-child td {
            border: none
        }
        .invoice table tfoot td {
            background: 0 0;
            border-bottom: none;
            white-space: nowrap;
            text-align: right;
            padding: 10px 20px;
            border-top: 1px solid #aaa;
            font-weight: bold;
        }
        .invoice table tfoot tr:first-child td {
            border-top: none
        }
        /* .invoice table tfoot tr:last-child td {
            color: #036;
            border-top: 1px solid #036
        } */
        .invoice table tfoot tr td:first-child {
            border: none
        }
        .invoice footer {
            width: 100%;
            text-align: center;
            color: #777;
            border-top: 1px solid #aaa;
            padding: 8px 0
        }
        .invoice a {
            content: none !important;
            text-decoration: none !important;
            color: #036 !important;
        }
        .page-header,
        .page-header-space {
            height: 100px;
        }
        .page-footer,
        .page-footer-space {
            height: 20px;

        }
        .page-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            color: #777;
            border-top: 1px solid #aaa;
            padding: 8px 0
        }
        .page-header {
            position: fixed;
            top: 0mm;
            width: 100%;
            border-bottom: 1px solid black;
        }
        .page {
            page-break-after: always;
        }
        .dashed-border{
            width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
        }
        @media screen {
            .no_screen {display: none;}
            .no_print {display: block;}
            thead {display: table-header-group;}
            tfoot {display: table-footer-group;}
            button {display: none;}
            body {margin: 0;}
        }
        @media print {
            body,
            html {
                /* background: #fff !important; */
                -webkit-print-color-adjust: exact !important;
                font-family: sans-serif;
                /* font-size: 12px !important; */
                margin-bottom: 100px !important;
            }
            .m-0 {
                margin: 0 !important;
            }
            h1,
            h2,
            h3,
            h4,
            h5,
            h6 {
                margin: 0 !important;
            }
            .no_screen {
                display: block !important;
            }
            .no_print {
                display: none;
            }
            a {
                content: none !important;
                text-decoration: none !important;
                color: #036 !important;
            }
            .text-center {
                text-align: center !important;
            }
            .text-left {
                text-align: left !important;
            }
            .text-right {
                text-align: right !important;
            }
            .float-left {
                float: left !important;
            }
            .float-right {
                float: right !important;
            }
            .text-bold {
                font-weight: bold !important;
            }
            .invoice {
                /* font-size: 11px!important; */
                overflow: hidden !important;
                background: #fff !important;
                margin-bottom: 100px !important;
            }
            .invoice footer {
                position: absolute;
                bottom: 0;
                left: 0;
                /* page-break-after: always */
            }
            /* .invoice>div:last-child {
                page-break-before: always
            } */
            .hidden-print {
                display: none !important;
            }
            .dashed-border{
                width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
            }
        }
        @page {
            /* size: auto; */
            margin: 5mm 5mm;
        }
    </style>
@endpush
<div class="modal fade" id="invoice_view_modal" tabindex="-1" role="dialog" aria-labelledby="invoice_view_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoice_view_modalLabel">Purchase Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="invoice_data">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
