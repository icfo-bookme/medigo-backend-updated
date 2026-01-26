$('.selectpicker').selectpicker({
    dropupAuto: false
}); //Initialize selectpicker

function number_format(num = 0) {
    return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}


function showFormModal(modal_title, btn_text) {
    $('#store_or_update_form')[0].reset();
    $('#store_or_update_form #update_id').val('');
    $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
    $('#store_or_update_form').find('.error').remove();
    $('#store_or_update_form .selectpicker').selectpicker('refresh');
    $('#store_or_update_form table tbody').find("tr:gt(0)").remove();

    $('#store_or_update_modal').modal({
        keyboard: false,
        backdrop: 'true',
    });
    $('#store_or_update_modal .modal-title').html('<i class="fas fa-plus-square text-white"></i> ' + modal_title);
    $('#store_or_update_modal #save-btn').text(btn_text);
}

function select_all() {
    if ($('#select_all:checked').length === 1) {
        $('.select_data').prop('checked', true);
    } else {
        $('.select_data').prop('checked', false);
    }
    toggleButtons();
}

function select_single_item() {
    var total = $('.select_data').length; // Count total checkboxes
    var total_checked = $('.select_data:checked').length; // Count total checked checkboxes

    (total == total_checked) ? $('#select_all').prop('checked', true): $('#select_all').prop('checked', false);
    toggleButtons();
}

function toggleButtons() {
    var total_checked = $('.select_data:checked').length;

    if (total_checked > 0) {
        $('.delete_btn').removeClass('d-none');
        $('.status_btn').removeClass('d-none');
        $('.bulk_approve_btn').removeClass('d-none');
        $('.update_btn').removeClass('d-none');
        $('.send_noti_btn').removeClass('d-none');
    } else {
        $('.delete_btn').addClass('d-none');
        $('.status_btn').addClass('d-none');
        $('.bulk_approve_btn').addClass('d-none');
        $('.update_btn').addClass('d-none');
        $('.send_noti_btn').addClass('d-none');
    }
}

function notification(status, message) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        onOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: status,
        title: message
    });
}

function multiple_notification(status, message) {
    Toastify({
        text: message,
        className: status,
        style: {
            background: "linear-gradient(to right, #1E1E1C, #E82327)",

        },
        duration: 3000,
        stopOnFocus: true,
    }).showToast();

}


function store_or_update_data(table, method, url, formData) {

    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        dataType: "JSON",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function() {
            $('#save-btn').addClass('spinner spinner-white spinner-right');
        },
        complete: function() {

            $('#save-btn').removeClass('spinner spinner-white spinner-right');
        },
        success: function(data) {
            console.log('success');

            if (data.status === 'success') {
                notification(data.status, data.message);
                table.ajax.reload();
                $('#store_or_update_modal').modal('hide');
            } else {
                notification(data.status, data.message);
            }
        },
        error: function(xhr) {
            console.log(xhr.responseJSON.errors)
            $('#save-btn').prop('disabled', false).removeClass(
                'spinner spinner-white spinner-right');
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let allErrors =
                    'The given data was invalid. Please check the errors below:\n';
                $.each(xhr.responseJSON.errors, function(key, value) {
                    allErrors += value[0] + '\n';



                    let element = $('#store_or_update_form').find('#' + key);

                    // Remove any existing error messages before adding new ones
                    element.removeClass('is-invalid'); // Remove previous invalid class
                    element.parent().find('.error').remove(); // Remove previous error messages

                    // Add the new error class and error message
                    element.addClass('is-invalid');
                    element.parent().append(
                        '<small class="error text-danger">' + value[0] +
                        '</small>'
                    );
                });
                // notification('error', allErrors);
            } else {
                notification('error', xhr.responseJSON.message ||
                    'An unexpected error occurred.');
                console.error(xhr.responseText);
            }
        }
    });
}

function delete_data(id, url, table, row, name) {
    Swal.fire({
        title: 'Are you sure to delete ' + name + ' data?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: url,
                type: "POST",
                data: { id: id, name: name, _token: _token },
                dataType: "JSON",
            }).done(function(response) {
                if (response.status === "success") {
                    Swal.fire("Deleted", response.message, "success").then(function() {
                        table.row(row).remove().draw(false);
                    });
                }
                if (response.status === "error") {
                    Swal.fire('Oops...', response.message, "error");
                }
            }).fail(function() {
                Swal.fire('Oops...', "Somthing went wrong with ajax!", "error");
            });
        }
    });
}

function bulk_delete(ids, url, table, rows) {
    Swal.fire({
        title: 'Are you sure to delete all checked data?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete all!'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    ids: ids,
                    _token: _token
                },
                dataType: "JSON",
            }).done(function(response) {

                if (response.status === "success") {
                    Swal.fire("Deleted", response.message, "success").then(function() {

                        table.rows(rows).remove().draw(false);
                        $('#select_all').prop('checked', false);
                        $('.delete_btn').addClass('d-none');
                    });
                }
                if (response.status === "error") {
                    Swal.fire('Oops...', response.message, "error");
                }
            }).fail(function() {
                Swal.fire('Oops...', "Somthing went wrong with ajax!", "error");
            });
        }
    });
}


function change_status(id, url, table, row, name, status) {
    Swal.fire({
        title: 'Are you sure to change ' + name + ' status?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: url,
                type: "POST",
                data: { id: id, status: status, name: name, _token: _token },
                dataType: "JSON",
            }).done(function(response) {
                if (response.status === "success") {
                    Swal.fire("Changed", response.message, "success").then(function() {
                        table.row(row).remove().draw(false);
                    });
                }
                if (response.status === "error") {
                    Swal.fire('Oops...', response.message, "error");
                }
            }).fail(function() {
                Swal.fire('Oops...', "Somthing went wrong with ajax!", "error");
            });
        }
    });
}