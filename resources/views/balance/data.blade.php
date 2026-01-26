<div class="col-md-12 d-flex justify-content-between">
    <div class="col-md-6">
        <button disabled class="btn btn-success w-100">Cash In (+)</button>
        <table id="" class="table table-bordered table-hover">
            <thead class="bg-primary">
            <tr>
                <th>Invoice</th>
                <th>Type</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
                @foreach($cashInInvoices as $in)
                    <tr>
                        <td>{{ $in->voucher_no }}</td>
                        <td>{{ $in->voucher_type }}</td>
                        <td>{{ $in->amount }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-md-6">
        <button disabled class="btn btn-danger w-100">Cash Out (-)</button>
        <table id="" class="table table-bordered table-hover">
            <thead class="bg-primary">
            <tr>
                <th>Invoice</th>
                <th>Type</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cashoutInvoices as $out)
                <tr>
                    <td>{{ $out->voucher_no }}</td>
                    <td>{{ $out->voucher_type }}</td>
                    <td>{{ $out->amount }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

