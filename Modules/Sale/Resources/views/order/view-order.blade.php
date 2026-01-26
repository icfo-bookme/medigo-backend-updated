<div class="modal fade" id="order_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">

        <!-- Modal Content -->
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header bg-primary">
                <h3 class="modal-title text-white" id="model-1"><i class="fas fa-eye text-white"></i> <span>Sales Person Details</span></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <!-- /modal header -->

            <!-- Modal Body -->
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="order_list">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Sale Status</th>
                                        <th>Invoice No.</th>
                                        <th>Total</th>
                                        <th>Order Discount</th>
                                        <th>Grand Total</th>
{{--                                        <th>Net Total</th>--}}
                                        <th>Paid Amount</th>
{{--                                        <th>Due Amount</th>--}}
                                        <th>Date</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!-- Your data rows will be appended here dynamically -->
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /modal body -->

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
            <!-- /modal footer -->
        </div>
        <!-- /modal content -->

    </div>
</div>
