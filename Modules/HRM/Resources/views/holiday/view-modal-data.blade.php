<div class="col-md-9">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Title</b></td>
                <td><b>:</b></td>
                <td>{{ $data->title }}</td>

                <td><b>Type</b></td>
                <td><b>:</b></td>
                <td>{{ TYPE_STATUS[$data->type] }}</td>
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
                <td><b>Description</b></td>
                <td><b>:</b></td>
                <td colspan="4">{{ $data->description }}</td>
            </tr>

        </table>
    </div>
</div>
