<div class="col-md-9">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Employee</b></td>
                <td><b>:</b></td>
                <td>{{ $data->employee->name }}</td>

                <td><b>Id</b></td>
                <td><b>:</b></td>
                <td>{{ $data->employee->employee_id }}</td>
            </tr>
            <tr>
                <td><b>Shift</b></td>
                <td><b>:</b></td>
                <td>{{ $data->shift->name }}</td>

                <td><b>Check In Time</b></td>
                <td><b>:</b></td>
                <td>{{ date("d-m-Y H:i:s a", strtotime($data->check_in_date . ' ' . $data->check_in_time)) }}</td>
            </tr>

            <tr>
                <td><b>Check Out Time</b></td>
                <td><b>:</b></td>
                <td>{{ date("d-m-Y H:i:s a", strtotime($data->check_out_date . ' ' . $data->check_out_time)) }}</td>

                <td><b>Attended Hours</b></td>
                <td><b>:</b></td>
                <td>{{ $data->working_hour }}</td>
            </tr>
            <tr>
                <td><b>Approved By</b></td>
                <td><b>:</b></td>
                <td>{{ $data->approved_by ?? '' }}</td>
                <td><b>Note</b></td>
                <td><b>:</b></td>
                <td>{{ $data->approve_remarks ?? '' }}</td>
            </tr>

        </table>
    </div>
</div>
