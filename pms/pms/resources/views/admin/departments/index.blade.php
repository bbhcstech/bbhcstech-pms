@extends('admin.layout.app')

@section('title', 'Departments')

@section('content')
<div class="container">
    &nbsp;
    <div class="d-flex justify-content-between mb-3 align-items-center">
        <h4 class="mb-0">Sub Departments</h4>

        <!-- header actions: Add only (desktop) -->
        <div class="d-none d-md-flex align-items-center gap-2" id="header-actions">
            <a href="{{ route('departments.create') }}" class="btn btn-primary">Add Sub Department</a>
        </div>
    </div>

    {{-- error / success messages --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- topbar: DataTables buttons (left) + search + (mobile actions) -->
    <div class="dt-topbar mb-3 d-flex justify-content-between align-items-center">
        <div id="dt-buttons-wrapper"></div>

        <div class="d-flex align-items-center">
            <div id="dt-search-wrapper" class="me-3"></div>

            <!-- mobile actions (visible on small screens) -->
            <div class="d-md-none">
                <a href="{{ route('departments.create') }}" class="btn btn-primary me-2 mb-2">Add</a>
                <button id="bulk-delete-btn-mobile" class="btn btn-danger mb-2">Delete Selected</button>
            </div>
        </div>
    </div>

    <table id="deptTable" class="table table-bordered">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all" /></th>
                <th>#</th>
                <th>Sub Department Code</th>
                <th>Sub Department</th>
                <th>Parent Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $index => $dept)
                <tr>
                    <td>
                        <input type="checkbox" name="bulk_ids[]" class="row-checkbox" value="{{ $dept->id }}">
                    </td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dept->dpt_code }}</td>
                    <td>{{ $dept->dpt_name }}</td>
                    <td>{{ $dept->parent?->dpt_name ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('departments.edit', $dept) }}" class="btn btn-sm btn-warning">Edit</a>

                        <form method="POST" action="{{ route('departments.destroy', $dept) }}" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- footer: row 1 -> info + pagination, row 2 -> Delete Selected under info -->
    <div id="dt-footer-wrapper" class="mt-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
            <div id="dt-info-wrapper" class="mb-2 mb-md-0"></div>
            <div id="dt-paginate-wrapper"></div>
        </div>

        <!-- button sits on its own line UNDER the info text -->
        <div class="text-start">
            <button id="bulk-delete-btn" class="btn btn-danger">
                Delete Selected
            </button>
        </div>
    </div>
</div>
@endsection


@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
    .dt-buttons .dt-button {
        margin-right: 10px;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 14px;
    }
    table.dataTable { width: 100% !important; }
    .dt-buttons { margin-bottom: 12px; }

    /* topbar styling */
    .dt-topbar { padding: 6px 0; }
    #dt-buttons-wrapper .dt-buttons { margin: 0; }
    #dt-search-wrapper .dataTables_filter { margin: 0; }

    /* header actions placement */
    #header-actions { gap: .75rem; }

    @media (max-width: 767px) {
        #header-actions { display: none !important; }
    }

    /* footer styling */
    #dt-footer-wrapper { padding-top: 6px; }
    #dt-paginate-wrapper .pagination { margin: 0; }
</style>
@endsection


@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function () {

    if ($.fn.dataTable && !$.fn.dataTable.isDataTable('#deptTable')) {
        var table = $('#deptTable').DataTable({
            dom: 'Bfrtip',
            paging: true,
            pageLength: 10,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            responsive: true,

            buttons: [
                { extend: 'copyHtml5', text: 'Copy', exportOptions: { columns: [1,2,3,4] } },
                { extend: 'csvHtml5', text: 'CSV', exportOptions: { columns: [1,2,3,4] } },
                { extend: 'excelHtml5', text: 'Excel', exportOptions: { columns: [1,2,3,4] } },
                { extend: 'pdfHtml5', text: 'PDF', pageSize: 'A4', exportOptions: { columns: [1,2,3,4] } },
                { extend: 'print', text: 'Print', exportOptions: { columns: [1,2,3,4] } }
            ],

            columnDefs: [
                { orderable: false, targets: 0 },
                { orderable: false, targets: -1 }
            ],

            initComplete: function() {
                var api = this.api();
                var container = $(api.table().container());

                // topbar
                container.find('.dt-buttons').appendTo('#dt-buttons-wrapper');
                container.find('.dataTables_filter').appendTo('#dt-search-wrapper');

                // footer row 1: info + pagination
                container.find('.dataTables_info').appendTo('#dt-info-wrapper');
                container.find('.dataTables_paginate').appendTo('#dt-paginate-wrapper');

                // mobile delete reuses same handler
                $('#bulk-delete-btn-mobile').on('click', function(){
                    $('#bulk-delete-btn').trigger('click');
                });
            }
        });
    }

    // select all toggle
    $(document).on('change', '#select-all', function() {
        var checked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', checked);
    });

    // update select-all when rows toggled
    $(document).on('change', '.row-checkbox', function() {
        var total = $('.row-checkbox').length;
        var checked = $('.row-checkbox:checked').length;
        $('#select-all').prop('checked', total === checked);
    });

    // bulk delete click
    $(document).on('click', '#bulk-delete-btn', function() {
        var ids = $('.row-checkbox:checked')
            .map(function() { return $(this).val(); })
            .get()
            .filter(Boolean);

        if (!ids.length) {
            alert('Select at least one department to delete.');
            return;
        }
        if (!confirm('Delete selected departments? This action cannot be undone.')) return;

        $.ajax({
            url: "{{ route('departments.bulk-delete') }}",
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify({ bulk_ids: ids }),
            success: function(res) {
                if (res.status === 'success') {

                    // remove only actually deleted IDs (because some may be blocked)
                    if (res.deleted_ids && res.deleted_ids.length) {
                        res.deleted_ids.forEach(function(id){
                            $('.row-checkbox[value="'+id+'"]').closest('tr').remove();
                        });

                        if ($.fn.dataTable.isDataTable('#deptTable')) {
                            $('#deptTable').DataTable().rows().invalidate().draw(false);
                        }
                    }

                    $('#select-all').prop('checked', false);

                    alert(res.message || (res.deleted + ' item(s) deleted'));
                } else {
                    alert(res.message || 'Bulk delete failed');
                }
            },
            error: function(xhr) {
                var msg = 'Bulk delete failed';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });
});
</script>
@endsection
