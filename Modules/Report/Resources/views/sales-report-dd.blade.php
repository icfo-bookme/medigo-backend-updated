@extends('layouts.app')
@section('title', $page_title)
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title">
                        <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row justify-content-center">
                            <x-form.textbox labelName="Invoice No." name="invoice_no" col="col-md-4" />
                            <div class="form-group col-md-4">
                                <label for="name">Choose Your Date</label>
                                <div class="input-group">
                                    <input type="text" class="form-control daterangepicker-filed">
                                    <input type="hidden" id="start_date" name="start_date">
                                    <input type="hidden" id="end_date" name="end_date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div style="margin-top:28px;">
                                    <div style="margin-top:28px;">
                                        <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon mr-2 float-left" type="button" data-toggle="tooltip" data-theme="dark" title="{{__('file.Reset')}}"><i class="fas fa-undo-alt"></i></button>
                                        <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon  float-left" type="button" data-toggle="tooltip" data-theme="dark" title="{{__('file.Search')}}"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Invoice No.</th>
                                        <th>Customer Name</th>
                                        <th>Item</th>
                                        <th>Total Qty</th>
                                        <th>Order Tax</th>
                                        <th>Order Discount</th>
                                        <th>Shipping Cost</th>
                                        <th>Net Total</th>
                                        <th>Grand Total</th>
                                        <th>Paid Amount</th>
                                        <th>Date</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                    <tr class="bg-primary">
                                        <th></th>
                                        <th></th>
                                        <th style="text-align: right !important;font-weight:bold;">Total</th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{asset('plugins/custom/datatables/datatables.bundle.js')}}" type="text/javascript"></script>
    <script>
        let table;
        $(document).ready(function(){

            table = $('#dataTable').DataTable({
                "processing": true,
                "serverSide": true,
                "order"     : [],
                "responsive": true,
                "bInfo"     : true,
                "bFilter"   : false,
                "lengthMenu": [
                    [5, 10, 15, 25, 50, 100, 1000, 10000, -1],
                    [5, 10, 15, 25, 50, 100, 1000, 10000, "All"]
                ],
                "pageLength": 25,
                "language"  : {
                    processing : `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
                    emptyTable : '<strong class="text-danger">{{__('file.No Data Found')}}</strong>',
                    infoEmpty  : '',
                    zeroRecords: '<strong class="text-danger">{{__('file.No Data Found')}}</strong>'
                },
                "ajax": {
                    "url" : "{{route('sales.report.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.invoice_no   = $("#form-filter #invoice_no").val();
                        data.start_date   = $("#form-filter #start_date").val();
                        data.end_date     = $("#form-filter #end_date").val();
                        data._token         = _token;
                    }
                },
                "columnDefs"       : [{
                    "orderable": false,
                    "targets"  : [0,1,2,3,4,5,6,7,8,9,10,11],
                    "className": "text-center"
                }],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
                "buttons": [{
                    'extend':'colvis','className':'btn btn-secondary btn-sm text-white','text':'{{__('file.Column')}}','columns': ':gt(0)'
                }, {
                    "extend"       : 'print',
                    'text'         :'{{'Print'}}',
                    'className'    :'btn btn-secondary btn-sm text-white',
                    "title"        : "{{ $page_title }} List",
                    "orientation"  : "landscape",
                    "pageSize"     : "A4",
                    "exportOptions": {
                        columns    : function (index, data, node) {
                            return table.column(index).visible();
                        }
                    },
                    customize: function (win) {
                        $(win.document.body).addClass('bg-white');
                        $(win.document.body).find('table thead').css({'background':'#034d97'});
                        $(win.document.body).find('table tfoot tr').css({'background-color':'#034d97'});
                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('h1').css('font-size', '15px');
                        $(win.document.body).find('table').css( 'font-size', 'inherit' );
                    },
                    footer:true
                },
                    {
                        "extend"       : 'csv',
                        'text'         :'{{'CSV'}}',
                        'className'    :'btn btn-secondary btn-sm text-white',
                        "title"        : "{{ $page_title }} List",
                        "filename"     : 'Customers Ledger From ' +$('#form-filter #start_date').val() + ' To ' +$('#form-filter #end_date').val(),
                        "exportOptions": {
                            columns    : function (index, data, node) {
                                return table.column(index).visible();
                            }
                        },
                        footer:true
                    }, {
                        "extend"       : 'excel',
                        'text'         :'{{'Excel'}}',
                        'className'    :'btn btn-secondary btn-sm text-white',
                        "title"        : "{{ $page_title }} List",
                        "filename"     : 'Customers Ledger From ' +$('#form-filter #start_date').val() + ' To ' +$('#form-filter #end_date').val(),
                        "exportOptions": {
                            columns    : function (index, data, node) {
                                return table.column(index).visible();
                            }
                        },
                        footer:true
                    }, {
                        "extend"       : 'pdf',
                        'text'         :'{{'PDF'}}',
                        'className'    :'btn btn-secondary btn-sm text-white',
                        "title"        : "{{ $page_title }} List",
                        "filename"     : 'Customers Ledger From ' +$('#form-filter #start_date').val() + ' To ' +$('#form-filter #end_date').val(),
                        "orientation"  : "portrait",
                        "pageSize"     : "A4",
                        "exportOptions": {
                            columns    : function (index, data, node) {
                                return table.column(index).visible();
                            }
                        },
                        footer:true,
                        customize: function(doc) {
                            doc.defaultStyle.fontSize       = 7; //<-- set fontsize to 16 instead of 10
                            doc.styles.tableHeader.fontSize = 7;
                            doc.styles.tableFooter.fontSize = 7;
                            doc.pageMargins                 = [5,5,5,5];
                        }
                    },
                ],
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    for (let index = 3; index <=10; index++) {
                        total = api
                            .column( index )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );
                        pageTotal = api
                            .column(index, { page: 'current'} )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );
                        $( api.column( index ).footer() ).html('= '+number_format(pageTotal));
                    }
                }
            });
            $('#btn-filter').click(function () {table.ajax.reload();});
            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('#form-filter .selectpicker').selectpicker('refresh');
                table.ajax.reload();
            });
        });
    </script>
@endpush
