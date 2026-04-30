@extends('layouts.registrar')

@section('title', 'PLP - Religion')
@section('page-title', 'RELIGION')

@section('content')

<div class="mb-4">
    <h4 class="fw-bold fs-5 text-dark mb-1 lh-sm">Religion Management</h4>
    <p class="text-secondary small mb-0">Manage all religions</p>
</div>

<div class="mb-4">
    <div class="card shadow-sm border-0 rounded-3 col-md-4 col-lg-3"
        style="display:inline-flex;">
        <div class="card-body d-flex align-items-center gap-3 p-3">
            <div class="flex-grow-1">
                <div class="fw-bold text-dark lh-1 mb-1" style="font-size:2rem;">
                    {{ $totalCount ?? 0 }}
                </div>
                <p class="text-secondary mb-0" style="font-size:0.85rem;">
                    Total Religions
                </p>
            </div>

            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                style="width:52px; height:52px; background:#eaf5ef;">
                <i class="bi bi-people-fill" style="font-size:1.4rem; color:#2d6a4f;"></i>
            </div>

        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-5">

    <div class="d-flex align-items-center justify-content-between px-4 pt-4 border-bottom position-relative">
    
        <div class="position-relative mb-2">
            <h5 class="fw-semibold mb-0" style="font-size:1rem;">
                Religions
            </h5>

            <div class="card-title-underline position-absolute start-0 w-100"
                style="height:2px; background:#2d6a4f; bottom:-20px;">
            </div>
        </div>

        <button class="btn btn-sm fw-medium text-white mb-3"
                style="background:#2d6a4f; border-color:#2d6a4f;"
                data-bs-toggle="modal" data-bs-target="#addReligionModal">
            Add Religion
        </button>
    </div>

    <div class="card-body px-4 pb-4 pt-3 mt-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <div id="table-length-control"></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div id="table-filter-control"></div>
                <div id="table-buttons-control"></div>
            </div>
        </div>

        <div class="table-responsive position-relative" style="min-height:200px;">
            <table class="table table-hover table-bordered w-100 mb-0" id="religionsTable" style="font-size:0.875rem;">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th>Name</th>
                        <th>Created&nbsp;By</th>
                        <th>Date&nbsp;Created</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 pt-3 border-top">
            <div id="table-info-control"></div>
            <div id="table-pagination-control"></div>
        </div>

    </div>
</div>

@include('registrar.process.religion.new')
@include('registrar.process.religion.edit')
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    var table = $('#religionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("registrar.process.religion.data") }}',
            type: 'GET',
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load data. Please refresh the page.' });
            }
        },
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'created_by', name: 'created_by', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: false,
        dom: "<'row'<'col-sm-12'B>>" +
             "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'copy', text: '<i class="fa fa-copy me-1"></i> Copy',    titleAttr: 'Copy to clipboard' },
            { extend: 'csv',  text: '<i class="fa fa-file-csv me-1"></i> CSV', titleAttr: 'Export to CSV' },
        ],
        order: [[1, 'asc']],
        language: {
            processing: '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x" style="color:#2d6a4f;"></i><br><span class="d-block mt-2" style="font-size:0.82rem;">Loading...</span></div>',
            emptyTable: 'No religions found',
            zeroRecords: 'No matching religions found',
            search: '',
            searchPlaceholder: 'Search...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 entries',
            infoFiltered:'(filtered from _MAX_ total entries)',
            paginate: {
                first: '&laquo;',
                previous: '&lsaquo;',
                next: '&rsaquo;',
                last: '&raquo;'
            }
        },
        initComplete: function () { moveControls(); },
        drawCallback:  function () { moveControls(); }
    });

    function moveControls() {
        var $len = $('div.dataTables_length');
        var $filter = $('div.dataTables_filter');
        var $buttons = $('div.dt-buttons');
        var $info = $('div.dataTables_info');
        var $pag = $('div.dataTables_paginate');

        if ($len.length) $('#table-length-control').empty().append($len.detach());
        if ($filter.length) $('#table-filter-control').empty().append($filter.detach());
        if ($buttons.length) $('#table-buttons-control').empty().append($buttons.detach());
        if ($info.length) $('#table-info-control').empty().append($info.detach());
        if ($pag.length) $('#table-pagination-control').empty().append($pag.detach());
    }

    $('#addReligionForm').on('submit', function (e) {
        e.preventDefault();

        var $btn = $('#saveReligionBtn');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Saving...');

        $('#religion_name').removeClass('is-invalid');
        $('#religion_name_error').text('');

        $.ajax({
            url: '{{ route("registrar.process.religion.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    $('#addReligionModal').modal('hide');
                    $('#addReligionForm')[0].reset();
                    Swal.fire({ icon: 'success', title: 'Success!', text: response.message });
                    table.ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $('#religion_name').addClass('is-invalid');
                        $('#religion_name_error').text(errors.name[0]);
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Save');
            }
        });
    });

    $('#addReligionModal').on('hidden.bs.modal', function () {
        $('#addReligionForm')[0].reset();
        $('#religion_name').removeClass('is-invalid');
        $('#religion_name_error').text('');
    });

    $(document).on('click', '.edit-religion', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            url: '{{ url("registrar/process/religion") }}/' + id + '/edit',
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (response) {
                if (response.success) {
                    $('#edit_religion_id').val(response.religion.id);
                    $('#edit_religion_name').val(response.religion.name);
                    $('#editReligionModal').modal('show');
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not load religion data.' });
            }
        });
    });

    $('#editReligionForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#edit_religion_id').val();
        var $btn = $('#updateReligionBtn');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Updating...');

        $('#edit_religion_name').removeClass('is-invalid');
        $('#edit_religion_name_error').text('');

        $.ajax({
            url: '{{ url("registrar/process/religion") }}/' + id,
            type: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    $('#editReligionModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Success!', text: response.message });
                    table.ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $('#edit_religion_name').addClass('is-invalid');
                        $('#edit_religion_name_error').text(errors.name[0]);
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Update');
            }
        });
    });

    $('#editReligionModal').on('hidden.bs.modal', function () {
        $('#editReligionForm')[0].reset();
        $('#edit_religion_name').removeClass('is-invalid');
        $('#edit_religion_name_error').text('');
    });

    $(document).on('click', '.delete-religion', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will permanently delete the religion!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("registrar/process/religion") }}/' + id,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (data) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message });
                        table.ajax.reload(null, false);
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Error deleting religion!' });
                    }
                });
            }
        });
    });

});
</script>
@endpush