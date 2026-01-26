<div class="col-md-9">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Employee</b></td>
                <td><b>:</b></td>
                <td>{{ $data->employee->name }}</td>

                <td><b>Hours</b></td>
                <td><b>:</b></td>
                <td>{{ $data->working_hour }}</td>
            </tr>
            <tr>
                <td><b>Starting Time</b></td>
                <td><b>:</b></td>
                <td>{{ date("d-m-Y H:i:s a", strtotime($data->start_date . ' ' . $data->start_time)) }} </td>

                <td><b>Ending Time</b></td>
                <td><b>:</b></td>
                <td>{{ date("d-m-Y H:i:s a", strtotime($data->end_date . ' ' . $data->end_time)) }}</td>
            </tr>

            <tr>
                <td><b>Created By</b></td>
                <td><b>:</b></td>
                <td>{{ $data->created_by }}</td>

                <td><b>Modified By</b></td>
                <td><b>:</b></td>
                <td>{{ $data->modified_by }}</td>
            </tr>
            <tr>
                <td><b>Approved By</b></td>
                <td><b>:</b></td>
                <td>{{ $data->approved_by }}</td>
                <td><b>Note</b></td>
                <td><b>:</b></td>
                <td>{{ $data->approve_remarks }}</td>
            </tr>

        </table>
    </div>
</div>
