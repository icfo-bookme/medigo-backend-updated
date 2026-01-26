<div class="col-md-9">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Leave Category</b></td>
                <td><b>:</b></td>
                <td>{{ $data->leaveCategory->leave_category }}</td>

                <td><b>Type</b></td>
                <td><b>:</b></td>
                <td>{{ $data->is_paid == 1 ? 'Paid': 'Unpaid' }}</td>
            </tr>
            <tr>
                <td><b>Employee</b></td>
                <td><b>:</b></td>
                <td>{{ $data->employee->name }}</td>

                <td><b>Purpose</b></td>
                <td><b>:</b></td>
                <td>{{ $data->notes }}</td>
            </tr>
            <tr>
                <td><b>Start Date</b></td>
                <td><b>:</b></td>
                <td>{{ date("d-m-Y", strtotime($data->start_date)) }}</td>

                <td><b>End Date</b></td>
                <td><b>:</b></td>
                <td>{{ date("d-m-Y", strtotime($data->end_date)) }}</td>
            </tr>
            <tr>
                <td><b>Period</b></td>
                <td><b>:</b></td>
                <td colspan="4">{{ $data->duration }} days</td>
            </tr>

        </table>
    </div>
</div>
