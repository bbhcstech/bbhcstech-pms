@extends('admin.layout.app')
@section('title', 'Awards List')

@section('content')
<div class="container py-4">
    
    <!-- Duration Filter with Clear Button -->
    <div class="d-flex align-items-center mb-3" style="gap:10px;">

        <label for="datatableRange" class="mb-0 f-14 text-dark-grey me-2">
            Duration
        </label>

        <!-- Larger Duration Field -->
        <div class="flex-grow-1">
            <input type="text" class="form-control p-2 f-14" 
                   id="datatableRange" 
                   placeholder="Start Date to End Date" autocomplete="off">
        </div>

        <!-- Clear Button beside -->
        <button type="button" id="clearFilter" class="btn btn-outline-danger f-14 ms-2">
            <i class="bi bi-x-circle"></i> &nbsp; Clear
        </button>
    </div>

    <div class="d-flex align-items-center mb-3
        @if(auth()->user()->role === 'admin') justify-content-between @else justify-content-end @endif">

        <!-- Left side: Add Appreciation button (only for admin) -->
        @if(auth()->user()->role === 'admin')
            <div>
                <a href="{{ route('awards.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> &nbsp; Add Appreciation
                </a>
            </div>
        @endif

        <!-- Right side: Filters / Icon buttons -->
        <div class="d-flex align-items-center gap-3">
            
            <div class="btn-group align-items-center" role="group">
                    <!-- Main bulk action (kept for status) -->
                    <select id="bulkAction" class="form-select form-select-sm" disabled>
                        <option value="">No Action</option>
                        <option value="status">Change Status</option>
                        <option value="delete">Delete</option>
                    </select>
                
                    <!-- Hidden status select -->
                    <select id="statusSelect" class="form-select form-select-sm ms-2" style="display:none;">
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                
                    <!-- Apply button -->
                    <button id="applyAction" class="btn btn-primary btn-sm ms-2" disabled>Apply</button>
                </div>
    
            <div class="btn-group" role="group">
                <a href="{{ route('awards.index') }}" 
                   class="btn btn-secondary f-14 btn-active" 
                   data-toggle="tooltip" data-original-title="Appreciation">
                    <i class="bi bi-trophy"></i>
                </a>
                <a href="{{ route('awards.apreciation-index') }}" 
                   class="btn btn-secondary f-14" 
                   data-toggle="tooltip" data-original-title="Award">
                    <i class="bi bi-award"></i>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background-color: #28a745; color: white; border-color: #28a745;">
            {{ session('success') }}
        </div>
    @endif

    <table id="awardTable" class="table table-bordered table-hover table-striped align-middle">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" id="selectAll">
                </th>
                <th>Given To</th>
                <th>Award Name</th>
                <th>Given On</th>
                @if(auth()->user()->role === 'admin')
                    <th>Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($awards as $award)
                <tr data-id="{{ $award->id }}">
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $award->id }}">
                    </td>
                    <td>{{ $award->user->name }}</td>
                    <td>{{ $award->appreciation->title ?? 'N/A' }}</td>
                    <td>{{ $award->award_date }}</td>
                    @if(auth()->user()->role === 'admin')
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton{{ $award->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $award->id }}">
                                <li>
                                    <a class="dropdown-item" href="{{ route('awards.edit', $award->id) }}">
                                        <i class="bi bi-pencil-square me-2"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <form action="{{ route('awards.destroy', $award->id) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this award?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item text-danger" type="submit">
                                            <i class="bi bi-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer row: showing text + separate bulk delete button -->
    <div class="d-flex align-items-center justify-content-between mt-3">
        <!--<div>-->
            <!-- Keep DataTables' own "Showing X to Y" if present, but we include a fallback -->
        <!--    <p class="mb-0">Showing {{ $awards->count() ? '1' : '0' }} to {{ $awards->count() }} of {{ $awards->count() }} entries</p>-->
        <!--</div>-->

        <!-- Separate Bulk Delete button -->
        @if(auth()->user()->role === 'admin')
        <div>
            <button id="bulkDeleteBtn" class="btn btn-danger" disabled>
                <i class="bi bi-trash me-1"></i> Bulk Delete
            </button>
        </div>
        @endif
    </div>

</div>

@push('js')

<!-- daterangepicker dependencies -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
$(function () {
    // Initialize DataTable once
    var table = $('#awardTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search awards..."
        }
    });

    // Hide Clear button initially
    $('#clearFilter').hide();

    // Date range filter: "Given On" is column index 3 (0-based)
    $.fn.dataTable.ext.search.push(function (settings, data) {
        var drp = $('#datatableRange').data('daterangepicker');
        var min = drp?.startDate;
        var max = drp?.endDate;
        var awardDateStr = data[3] || ''; // column 3

        // Only filter if input box actually has a value
        if (!$('#datatableRange').val() || !awardDateStr) return true;

        // Expecting YYYY-MM-DD stored in table, but be tolerant
        var awardDate = moment(awardDateStr, 'YYYY-MM-DD', true);
        if (!awardDate.isValid()) {
            awardDate = moment(awardDateStr);
        }
        if (!awardDate.isValid()) return true;

        return awardDate.isBetween(min, max, 'day', '[]');
    });

    // Initialize Date Range Picker without auto-updating input
    $('#datatableRange').daterangepicker({
        autoUpdateInput: false,
        locale: { cancelLabel: 'Clear' }
    });

    // Apply date
    $('#datatableRange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        $('#clearFilter').show();
        table.draw();
    });

    // Clear via datepicker Cancel
    $('#datatableRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        picker.setStartDate(moment().startOf('day'));
        picker.setEndDate(moment().endOf('day'));
        $('#clearFilter').hide();
        table.draw();
    });

    // Clear button click
    $('#clearFilter').on('click', function () {
        var drp = $('#datatableRange').data('daterangepicker');
        $('#datatableRange').val('');
        if (drp) {
            drp.setStartDate(moment());
            drp.setEndDate(moment());
        }
        $(this).hide();
        table.draw();
    });

    // Select/Deselect all & row checkbox changes
    $('#selectAll').on('click', function () {
        $('.row-checkbox').prop('checked', this.checked).trigger('change');
    });

    $(document).on('change', '.row-checkbox', function () {
        var selectedCount = $('.row-checkbox:checked').length;

        // toggle existing bulk action UI
        $('#bulkAction, #applyAction').prop('disabled', selectedCount === 0);

        // toggle new separate bulk delete button
        $('#bulkDeleteBtn').prop('disabled', selectedCount === 0);

        if (selectedCount === 0) {
            $('#statusSelect').hide().val('');
        }
        // keep selectAll checkbox in sync
        $('#selectAll').prop('checked', $('.row-checkbox').length === selectedCount);
    });

    // Show/hide status select
    $('#bulkAction').on('change', function () {
        if ($(this).val() === 'status') {
            $('#statusSelect').show();
        } else {
            $('#statusSelect').hide().val('');
        }
    });

    // Apply bulk action (status OR delete) from the dropdown/apply (unchanged)
    $('#applyAction').on('click', function () {
        var action = $('#bulkAction').val();
        var status = $('#statusSelect').val();
        var ids = $('.row-checkbox:checked').map(function () { return $(this).val(); }).get();

        if (!action || ids.length === 0) {
            alert("Please select at least one award and an action.");
            return;
        }
        if (action === 'status' && !status) {
            alert("Please select a status (Active/Inactive).");
            return;
        }
        if (action === 'delete' && !confirm("Are you sure to delete selected awards? This will also remove associated photos.")) {
            return;
        }

        // If delete, call dedicated bulk-delete route
        if (action === 'delete') {
            $.ajax({
                url: "{{ route('awards.bulk-delete') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ids: ids
                },
                success: function (res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert(res.message || "Failed to delete selected awards.");
                    }
                },
                error: function (xhr) {
                    var msg = "Something went wrong while deleting awards.";
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            });
            return;
        }

        // Otherwise handle status change (reuses existing route)
        $.ajax({
            url: "{{ route('awards.bulkAction') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                action: action,
                status: status,
                ids: ids
            },
            success: function () {
                $('#bulkAction').val('');
                $('#statusSelect').hide().val('');
                $('#applyAction').prop('disabled', true);
                $('.row-checkbox, #selectAll').prop('checked', false);
                location.reload();
            },
            error: function () {
                alert("Something went wrong while updating status!");
            }
        });
    });

    // NEW: Separate Bulk Delete button below table
    $('#bulkDeleteBtn').on('click', function () {
        var ids = $('.row-checkbox:checked').map(function () { return $(this).val(); }).get();
        if (ids.length === 0) {
            alert("Please select at least one award to delete.");
            return;
        }

        if (!confirm("Are you sure you want to permanently delete the selected awards? This will also remove associated photos.")) {
            return;
        }

        $.ajax({
            url: "{{ route('awards.bulk-delete') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                ids: ids
            },
            success: function (res) {
                if (res.success) {
                    // remove rows from the table without reload (nice UX)
                    ids.forEach(function(id){
                        var row = $('#awardTable').find('tr[data-id="'+id+'"]');
                        table.row(row).remove().draw(false);
                    });
                    // reset controls
                    $('#selectAll').prop('checked', false);
                    $('#bulkDeleteBtn').prop('disabled', true);
                    $('#bulkAction, #applyAction').prop('disabled', true).val('');
                    $('#statusSelect').hide().val('');
                } else {
                    alert(res.message || "Failed to delete selected awards.");
                }
            },
            error: function (xhr) {
                var msg = "Something went wrong while deleting awards.";
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });

});
</script>

@endpush

@endsection
