@extends('admin.layout.app')

@section('title', 'Leaves')

@section('content')
<div class="container mt-4">
    <h4>Leave Requests</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <!-- Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('leaves.index') }}">
            <div class="row g-3 align-items-end">

                <!-- Duration -->
                 <div class="col-md-3">
                    <label for="duration" class="form-label">Duration</label>
                    <input type="text"
                           name="duration"
                           id="duration"
                           class="form-control"
                           value="{{ request('duration') }}"
                           placeholder="Start Date to End Date"
                           autocomplete="off">
                </div>


                <!-- Button -->
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-outline-primary w-100 me-2">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>


    <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- Left Side: New Leave -->
        <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> &nbsp; New Leave
        </a>
    
        <!-- Right Side: Button Group -->
        <div class="btn-group" role="group" aria-label="Basic example">
            
            <div class="btn-group" role="group" aria-label="Basic example">
                <div class="d-flex align-items-center mb-3">
                    <select id="bulk-action" class="form-select w-auto me-2" disabled style="display: none;">
                        <option value="">-- Select Action --</option>
                        <option value="none">No Action</option>
                        <option value="change_status">Change Leave Status</option>
                        <option value="delete">Delete</option>
                    </select>
            
                    <select id="status-dropdown" class="form-select w-auto me-2" style="display: none;" disabled>
                        <option value="">-- Select Status --</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
            
                   <button id="apply-action" class="btn btn-primary" style="display: none;">Apply</button>

                </div>
            </div>

        
        &nbsp;

            <a href="" 
               class="btn btn-secondary f-14" 
               data-toggle="tooltip" 
               data-original-title="Table View">
                <i class="side-icon bi bi-list-ul"></i>
            </a>
    
            <a href="{{ route('leaves.calendar') }}" 
       class="btn btn-secondary f-14 {{ request()->routeIs('leaves.calendar') ? 'btn-active' : '' }}"
       data-toggle="tooltip" data-original-title="Calendar">
        <i class="side-icon bi bi-calendar"></i>
    </a>
    
        </div>
    </div>



       <table id="leaveTable" class="table table-bordered table-hover table-striped align-middle">
        <thead>
            <tr>
                 <th>
                <input type="checkbox" id="select-all">
                </th>
                <th>Employee</th>
                <th>Leave Date</th>
                <th> Duration</th>
                <th>Leave Status</th>
                <th>Leave Type</th>
                <th>Paid</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leaves as $leave)
                <tr>
                     <td>
                    <input type="checkbox" class="leave-checkbox" value="{{ $leave->id }}">
                     </td>
                    <td>{{ $leave->user?->name ?? 'N/A' }}</td>
                     <td>
                        @if($leave->start_date && $leave->end_date)
                            {{ $leave->start_date }}
                        @else
                            {{ $leave->date }}
                        @endif
                    </td>
                    <td> @if($leave->duration === 'multiple' && $leave->start_date && $leave->end_date)
                            {{ $leave->duration}} <br>
                            {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} days
                        @else
                          
                            {{ $leave->duration }}
                        @endif
                        
                    </td>
                    
                      <td>{{ ucfirst($leave->status) }}</td>
                   <td>
    @if($leave->duration === 'multiple')
        <!-- Button to trigger modal -->
        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#leaveModal{{ $leave->id }}">
            View Status
        </button>

        <!-- Modal -->
        <div class="modal fade" id="leaveModal{{ $leave->id }}" tabindex="-1" aria-labelledby="leaveModalLabel{{ $leave->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaveModalLabel{{ $leave->id }}">Total Leave ({{ $leave->user?->name ?? 'Unknown User' }})</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Leave Date</th>
                                    <th>Leave Type</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $start = \Carbon\Carbon::parse($leave->start_date);
                                    $end = \Carbon\Carbon::parse($leave->end_date);
                                @endphp

                                @for ($date = $start; $date->lte($end); $date->addDay())
                                    <tr>
                                        <td>{{ $date->format('d-m-Y (l)') }}</td>
                                        <td>{{ ucfirst($leave->type) }}</td>
                                        <td>Paid</td>
                                        <td>{{ ucfirst($leave->status) }}</td>
                                        <td>
                                            <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{ ucfirst($leave->type) }}
    @endif
</td>

                       <td>
                           @if(auth()->user()->role === 'employee')
                           @if($leave->paid == 0)
                                <span class="badge bg-danger">Unpaid</span>
                            @elseif($leave->paid == 1)
                                <span class="badge bg-success">Paid</span>
                            @endif
                            
                            @endif
                            @if(auth()->user()->role === 'admin')
                        <select class="form-select form-select-sm change-paid-status" 
                                data-leave-id="{{ $leave->id }}">
                            <option value="0" {{ $leave->paid == 0 ? 'selected' : '' }}>Unpaid</option>
                            <option value="1" {{ $leave->paid == 1 ? 'selected' : '' }}>Paid</option>
                        </select>
                        @endif 
                    </td>

                   <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton{{ $leave->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                    
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $leave->id }}">
                                
                                {{-- View --}}
                                <li>
                                    <a class="dropdown-item" href="{{ route('leaves.show', $leave->id) }}">
                                            <i class="bi bi-eye me-2"></i> View
                                        </a>

                                </li>
                    
                                {{-- Approve (Admin only) --}}
                                @if(auth()->user()->role == 'admin' && $leave->status !== 'approved')
                                <li>
                                    <form action="{{ route('leaves.updateStatus', $leave->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="dropdown-item text-success">
                                            <i class="bi bi-check2 me-2"></i> Approve
                                        </button>
                                    </form>
                                </li>
                                @endif
                    
                                {{-- Reject (Admin only) --}}
                                @if(auth()->user()->role == 'admin' && $leave->status !== 'rejected')
                                <li>
                                    <form action="{{ route('leaves.updateStatus', $leave->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="dropdown-item text-warning">
                                            <i class="bi bi-x-lg me-2"></i> Reject
                                        </button>
                                    </form>
                                </li>
                                @endif
                                @if(auth()->user()->role === 'admin')
                                {{-- Edit (only if not rejected) --}}
                                @if($leave->status !== 'rejected')
                                <li>
                                    <a class="dropdown-item" href="{{ route('leaves.edit', $leave->id) }}">
                                        <i class="bi bi-pencil-square me-2"></i> Edit
                                    </a>
                                </li>
                                @endif
                    
                                {{-- Delete --}}
                                <li>
                                    <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                                
                                
                                  @endif
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Bulk actions row: shows the count info and Bulk Delete button -->
    <div class="d-flex justify-content-between align-items-center mt-2">
        <!--<div id="dt-custom-info">Showing 1 to {{ $leaves->count() }} of {{ $leaves->count() }} entries</div>-->

        @if(auth()->user()->role === 'admin')
        <div>
            <button id="bulkDeleteBtn" class="btn btn-danger btn-sm" disabled>
                <i class="bi bi-trash"></i> Bulk Delete
            </button>
        </div>
        @endif
    </div>

</div>

@push('js')


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>

$(function () {
    const predefinedRanges = {
        'Today': [moment(), moment()],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    };

    $('#duration').daterangepicker({
        autoUpdateInput: false,
        showDropdowns: true,
        opens: 'left',
        locale: {
            format: 'YYYY-MM-DD',
            cancelLabel: 'Clear'
        },
        ranges: predefinedRanges
    });

    $('#duration').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(
            picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD')
        );
    });

    $('#duration').on('cancel.daterangepicker', function () {
        $(this).val('');
    });
});
</script>

<script>
$(document).on('change', '.change-paid-status', function () {
    let leaveId = $(this).data('leave-id');
    let status = $(this).val();

    $.ajax({
        url: "{{ route('leaves.updatePaidStatus') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            leave_id: leaveId,
            paid: status
        },
        success: function (response) {
            if (response.success) {
                alert("Leave status updated successfully!");
            } else {
                alert("Something went wrong!");
            }
        }
    });
});
</script>

<script>
$(document).ready(function () {
    // initialize datatable and keep reference
    var table = $('#leaveTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Start typing to search..."
        }
    });

    // update custom info on draw
    function updateCustomInfo() {
        var info = table.page.info();
        // DataTables returns start as zero-based index of first record on page. convert to 1-based for display.
        var start = (info.recordsDisplay === 0) ? 0 : info.start + 1;
        var end = info.end;
        var total = info.recordsDisplay;
        $('#dt-custom-info').text('Showing ' + start + ' to ' + end + ' of ' + total + ' entries');
    }
    updateCustomInfo();
    table.on('draw', updateCustomInfo);

    // select all checkbox
    $('#select-all').on('change', function () {
        // only toggle visible checkboxes (those in current page)
        var checked = $(this).prop('checked');
        $('#leaveTable tbody tr:visible').find('.leave-checkbox').prop('checked', checked).trigger('change');
    });

    // when row checkboxes change, toggle bulk delete btn and sync select-all
    $(document).on('change', '.leave-checkbox', function () {
        var anyChecked = $('.leave-checkbox:checked').length > 0;
        $('#bulkDeleteBtn').prop('disabled', !anyChecked);

        // sync the select-all for visible rows
        var visibleBoxes = $('#leaveTable tbody tr:visible').find('.leave-checkbox');
        if (visibleBoxes.length && visibleBoxes.length === visibleBoxes.filter(':checked').length) {
            $('#select-all').prop('checked', true);
        } else {
            $('#select-all').prop('checked', false);
        }

        // Also toggle the top bulk-action UI if you want to enable it when checkboxes selected
        toggleBulkAction();
    });

    // Bulk delete click
    $('#bulkDeleteBtn').on('click', function (e) {
        e.preventDefault();

        var selected = $('.leave-checkbox:checked').map(function () { return $(this).val(); }).get();

        if (selected.length === 0) {
            alert('Please select at least one leave.');
            return;
        }

        if (!confirm('Are you sure you want to delete ' + selected.length + ' selected leave(s)? This cannot be undone.')) {
            return;
        }

        // disable button to prevent double-click
        $('#bulkDeleteBtn').prop('disabled', true).text('Deleting...');

        $.ajax({
            url: "{{ route('leaves.bulk-delete') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                ids: selected
            },
            success: function (res) {
                if (res.success ?? true) {
                    alert(res.message ?? 'Deleted successfully');
                    // remove rows from DataTable instead of full reload
                    table.rows( $('.leave-checkbox:checked').closest('tr') ).remove().draw();
                    $('#bulkDeleteBtn').prop('disabled', true).text('Bulk Delete');
                } else {
                    alert(res.message || 'Something went wrong');
                    $('#bulkDeleteBtn').prop('disabled', false).text('Bulk Delete');
                }
            },
            error: function (xhr) {
                var msg = 'Error: ' + (xhr.responseJSON?.message || xhr.statusText);
                alert(msg);
                $('#bulkDeleteBtn').prop('disabled', false).text('Bulk Delete');
            }
        });
    });

    // existing bulk action UI toggles
    function toggleBulkAction() {
        let anyChecked = $('.leave-checkbox:checked').length > 0;

        if (anyChecked) {
            $('#bulk-action').show().prop('disabled', false);
            $('#apply-action').show();
        } else {
            $('#bulk-action').hide().prop('disabled', true).val('');
            $('#status-dropdown').hide().prop('disabled', true).val('');
            $('#apply-action').hide();
        }
    }

    // initial toggle run (in case some checkboxes are pre-checked)
    toggleBulkAction();

});
</script>

<script>
    $(document).on('change', '#bulk-action', function () {
        if ($(this).val() === 'change_status') {
            $('#status-dropdown').show();
        } else {
            $('#status-dropdown').hide();
        }
    });

    // Apply button
    $('#apply-action').on('click', function () {
        let action = $('#bulk-action').val();
        let status = $('#status-dropdown').val();
        let selected = $('.leave-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (selected.length === 0) {
            alert("Please select at least one leave.");
            return;
        }

        if (action === '') {
            alert("Please select an action.");
            return;
        }

        $.ajax({
            url: "{{ route('leaves.bulkAction') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                action: action,
                status: status,
                ids: selected
            },
            success: function (response) {
                alert(response.message);
                location.reload();
            }
        });
    });
</script>

@endpush

@endsection
