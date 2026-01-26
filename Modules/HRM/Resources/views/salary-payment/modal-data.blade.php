<div class="col-md-9">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Employee</b></td>
                <td><b>:</b></td>
                <td>{{ $data->employee->name }}</td>

                <td><b>Payment Amount</b></td>
                <td><b>:</b></td>
                <td>{{ $data->paid_amount }}</td>
            </tr>
            <tr>
                <td><b>Account</b></td>
                <td><b>:</b></td>
                <td>{{ $data->coa->name }}</td>
                <td><b>Created By</b></td>
                <td><b>:</b></td>
                <td>{{ $data->created_by }}</td>
            </tr>
        </table>
    </div>
</div>
