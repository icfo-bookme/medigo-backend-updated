<table id="dataTable" class="table table-bordered table-hover">
    <thead class="bg-primary">
        <tr>
            <th>{{ __('file.Employee Name') }}</th>
            <th>{{ __('file.Due Amount') }}</th>
            <th>{{ __('file.Paid Amount') }}</th>
            <th>{{ __('file.Payment Method') }}</th>
            <th>{{ __('file.Account') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($employees as $key => $emp)
            @php
                $due = $emp->credit - $emp->debit;
            @endphp
            <tr>
                <td>{{ $emp->name }} 
                
                    <input type="hidden" name="salary[{{ $key }}][employee_id]" id="salary_{{ $key }}_employee_id" class="form-control" value="{{ $emp->employee_id }}"></td>
                <td><input type="text" name="salary[{{ $key }}][due]" id="salary_{{ $key }}_due" class="form-control" value="{{ $due }}" readonly>
                </td>
                <td><input type="text" name="salary[{{ $key }}][paid_amount]" id="salary_{{ $key }}_paid_amount" class="form-control" value="0">
                </td>
                <td>
                    <Select class="from-control selectpicker" name="salary[{{ $key }}][payment_method]" id="salary_{{ $key }}_payment_method" onchange="account_list(this.value, {{ $key }})">
                        <option value="">Select Method</option>
                        @foreach (SALE_PAYMENT_METHOD as $lkey => $value)
                            <option value="{{ $lkey }}">{{ $value }}</option>
                        @endforeach
                    </Select>
                </td>
                <td>
                    <Select class="from-control selectpicker fcs" name="salary[{{ $key }}][account_id]" id="salary_{{ $key }}_account_id">
                    </Select>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="form-group col-md-12 text-center pt-5">
    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save
    </button>
</div>
