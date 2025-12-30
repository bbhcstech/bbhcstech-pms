@extends('admin.layout.app')

@section('title', 'Parent Departments')

@section('content')
<main class="main py-4">
    <div class="container">

        <div class="d-flex justify-content-between mb-3">
            <h4> Departments</h4>

            <div class="header-actions">
                <a href="{{ route('parent-departments.create') }}" class="btn btn-primary mb-3">
                     Department
                </a>
            </div>
        </div>

        {{-- Errors - for delete protection, bulk delete issues, etc --}}
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

        <table id="parentTable" class="table table-bordered">
            <thead>
            <tr>
                <th><input type="checkbox" id="select-all" /></th>
                <th>#</th>
                <th>Code</th>
                <th>Department Name</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($departments as $index => $dpt)
                <tr>
                    <td>
                        <input type="checkbox" name="bulk_ids[]" class="row-checkbox" value="{{ $dpt->id }}">
                    </td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dpt->dpt_code }}</td>
                    <td>{{ $dpt->dpt_name }}</td>
                    <td>
                        <a href="{{ route('parent-departments.edit', $dpt) }}" class="btn btn-sm btn-warning">
                            Edit
                        </a>

                        <form action="{{ route('parent-departments.destroy', $dpt) }}"
                              method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this department?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div id="bulk-delete-wrapper">
            <button id="bulk-delete-btn" class="btn btn-danger">Delete Selected</button>
        </div>

    </div>
</main>
@endsection


@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
    .dt-buttons .dt-button {
        margin-right: 10px;
        border: 1px solid #222;
        background: white;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
    }
    .dt-buttons { margin-bottom: 12px; }

    .dataTables_wrapper .dt-buttons { float: left !important; margin-bottom: 10px; }
    .dataTables_wrapper .dataTables_filter { float: right !important; }
    .dataTables_wrapper .dataTables_info { float: left !important; margin-top: 10px !important; }
    .dataTables_wrapper .dataTables_paginate { float: right !important; margin-top: 10px !important; }
    .dataTables_wrapper .clear { clear: both !important; }

    .header-actions {
        display:flex;
        gap:.5rem;
        align-items:center;
        position:relative;
        z-index:5;
    }

    #bulk-delete-wrapper {
        margin-top: -6px;
    }
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

    const table = $('#parentTable').DataTable({
        dom: 'Bfrtip',
        paging: true,
        ordering: true,
        searching: true,
        info: true,
        buttons: [
            { extend: 'copyHtml5',  text: 'Copy',  exportOptions: { columns: [1,2,3] } },
            { extend: 'csvHtml5',   text: 'CSV',   exportOptions: { columns: [1,2,3] } },
            { extend: 'excelHtml5', text: 'Excel', exportOptions: { columns: [1,2,3] } },
            { extend: 'pdfHtml5',   text: 'PDF',   exportOptions: { columns: [1,2,3] } },
            { extend: 'print',      text: 'Print', exportOptions: { columns: [1,2,3] } },
        ],
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: false, targets: -1 }
        ]
    });

    // Select all
    $(document).on('change', '#select-all', function() {
        $('.row-checkbox').prop('checked', $(this).is(':checked'));
    });

    $(document).on('change', '.row-checkbox', function() {
        $('#select-all').prop(
            'checked',
            $('.row-checkbox').length === $('.row-checkbox:checked').length
        );
    });

    // Bulk delete
    $('#bulk-delete-btn').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!ids.length) {
            alert('Select at least one department.');
            return;
        }

        if (!confirm('Delete selected department(s)?')) return;

        $.ajax({
            url: "{{ route('parent-departments.bulk-delete') }}",
            method: 'POST',
            data: { bulk_ids: ids, _token: '{{ csrf_token() }}' },
            success(res) {
                if (res.status === 'success') {
                    // Remove only actually deleted IDs (because some may be blocked)
                    if (res.deleted_ids && res.deleted_ids.length) {
                        res.deleted_ids.forEach(function(id) {
                            $('.row-checkbox[value="' + id + '"]')
                                .closest('tr')
                                .remove();
                        });
                        table.rows().invalidate().draw(false);
                    }
                    $('#select-all').prop('checked', false);
                    alert(res.message);
                } else {
                    alert(res.message || 'Something went wrong.');
                }
            },
            error(xhr) {
                let msg = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    });

});
</script>
@endsection
