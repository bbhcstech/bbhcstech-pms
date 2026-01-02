@extends('admin.layout.app')

@section('title', 'Leaves')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-2">Leave Management</h1>
            <p class="text-muted mb-0">Manage employee leave requests and approvals</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>New Leave Request
            </a>
        </div>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent py-3">
            <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Filter Leaves</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('leaves.index') }}" class="row g-3">
                <!-- Duration Filter -->
                <div class="col-lg-3 col-md-6">
                    <label for="duration" class="form-label">Date Range</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                        <input type="text"
                               name="duration"
                               id="duration"
                               class="form-control"
                               value="{{ request('duration') }}"
                               placeholder="Select date range"
                               autocomplete="off">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-lg-6 col-md-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Controls Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                <!-- Bulk Actions -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="d-flex align-items-center">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" id="select-all">
                            <label class="form-check-label small" for="select-all">Select All</label>
                        </div>

                        <select id="bulk-action" class="form-select form-select-sm w-auto" disabled style="display: none;">
                            <option value="">-- Bulk Action --</option>
                            <option value="none">No Action</option>
                            <option value="change_status">Change Status</option>
                            <option value="delete">Delete Selected</option>
                        </select>

                        <select id="status-dropdown" class="form-select form-select-sm w-auto ms-2" style="display: none;" disabled>
                            <option value="">Select Status</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>

                        <button id="apply-action" class="btn btn-sm btn-primary ms-2" style="display: none;">
                            <i class="bi bi-check-lg me-1"></i>Apply
                        </button>

                        @if(auth()->user()->role === 'admin')
                        <button id="bulkDeleteBtn" class="btn btn-sm btn-danger ms-2" disabled>
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                        @endif
                    </div>
                </div>

                <!-- View Toggle Buttons -->
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('leaves.index') }}"
                           class="btn btn-outline-primary {{ request()->routeIs('leaves.index') ? 'active' : '' }}"
                           data-bs-toggle="tooltip" title="Table View">
                            <i class="bi bi-list-ul"></i>
                        </a>
                        <a href="{{ route('leaves.calendar') }}"
                           class="btn btn-outline-primary {{ request()->routeIs('leaves.calendar') ? 'active' : '' }}"
                           data-bs-toggle="tooltip" title="Calendar View">
                            <i class="bi bi-calendar"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaves Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="leaveTable" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="ps-3">
                                <input type="checkbox" id="select-all-main">
                            </th>
                            <th>Employee</th>
                            <th>Leave Date</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Paid Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                            <tr>
                                <td class="ps-3">
                                    <input type="checkbox" class="leave-checkbox" value="{{ $leave->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs bg-light rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-person text-muted"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            {{ $leave->user?->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($leave->start_date && $leave->end_date)
                                        <span class="badge bg-light text-dark">{{ $leave->start_date }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">{{ $leave->date }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($leave->duration === 'multiple' && $leave->start_date && $leave->end_date)
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ ucfirst($leave->duration) }}
                                        </span>
                                        <div class="small text-muted mt-1">
                                            {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} days
                                        </div>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            {{ ucfirst($leave->duration) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger'
                                        ];
                                        $color = $statusColors[$leave->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($leave->duration === 'multiple')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#leaveModal{{ $leave->id }}">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </button>
                                    @else
                                        <span class="badge bg-light text-dark">{{ ucfirst($leave->type) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(auth()->user()->role === 'employee')
                                        @if($leave->paid == 0)
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Unpaid</span>
                                        @elseif($leave->paid == 1)
                                            <span class="badge bg-success bg-opacity-10 text-success">Paid</span>
                                        @endif
                                    @endif

                                    @if(auth()->user()->role === 'admin')
                                        <select class="form-select form-select-sm change-paid-status"
                                                data-leave-id="{{ $leave->id }}"
                                                style="width: 120px;">
                                            <option value="0" {{ $leave->paid == 0 ? 'selected' : '' }}>Unpaid</option>
                                            <option value="1" {{ $leave->paid == 1 ? 'selected' : '' }}>Paid</option>
                                        </select>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-light border"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('leaves.show', $leave->id) }}">
                                                    <i class="bi bi-eye text-primary me-2"></i>View Details
                                                </a>
                                            </li>

                                            @if(auth()->user()->role == 'admin' && $leave->status !== 'approved')
                                            <li>
                                                <form action="{{ route('leaves.updateStatus', $leave->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-check-circle text-success me-2"></i>Approve
                                                    </button>
                                                </form>
                                            </li>
                                            @endif

                                            @if(auth()->user()->role == 'admin' && $leave->status !== 'rejected')
                                            <li>
                                                <form action="{{ route('leaves.updateStatus', $leave->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-x-circle text-warning me-2"></i>Reject
                                                    </button>
                                                </form>
                                            </li>
                                            @endif

                                            @if(auth()->user()->role === 'admin')
                                                @if($leave->status !== 'rejected')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('leaves.edit', $leave->id) }}">
                                                        <i class="bi bi-pencil text-info me-2"></i>Edit
                                                    </a>
                                                </li>
                                                @endif

                                                <li><hr class="dropdown-divider"></li>

                                                <li>
                                                    <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-trash text-danger me-2"></i>Delete
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
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small" id="dt-custom-info">
            Showing {{ $leaves->count() }} records
        </div>
    </div>
</div>

<!-- Multiple Leaves Modal -->
@foreach($leaves as $leave)
@if($leave->duration === 'multiple')
<div class="modal fade" id="leaveModal{{ $leave->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-week me-2"></i>
                    Leave Details - {{ $leave->user?->name ?? 'Unknown User' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Day</th>
                                <th>Type</th>
                                <th>Paid</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $start = \Carbon\Carbon::parse($leave->start_date);
                                $end = \Carbon\Carbon::parse($leave->end_date);
                            @endphp

                            @for ($date = $start; $date->lte($end); $date->addDay())
                                <tr>
                                    <td class="ps-3">{{ $date->format('d-m-Y') }}</td>
                                    <td>{{ $date->format('l') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ ucfirst($leave->type) }}</span>
                                    </td>
                                    <td>
                                        @if($leave->paid == 1)
                                            <span class="badge bg-success bg-opacity-10 text-success">Paid</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColors[$leave->status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusColors[$leave->status] ?? 'secondary' }}">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
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
</div>
@endif
@endforeach

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .avatar-xs {
        width: 32px;
        height: 32px;
    }
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        color: #6c757d;
    }
    .table td {
        vertical-align: middle;
    }
    .badge.bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    .dropdown-menu {
        min-width: 180px;
    }
    .form-select-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .daterangepicker {
        z-index: 1055 !important;
    }
</style>
@endpush

@push('js')
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
                // Update the visual feedback
                $(this).closest('td').find('.badge').remove();
                var badgeClass = status == 1 ? 'success' : 'danger';
                var badgeText = status == 1 ? 'Paid' : 'Unpaid';
                $(this).after(`<span class="badge bg-${badgeClass} bg-opacity-10 text-${badgeClass} ms-2">${badgeText}</span>`);

                // Show success toast/alert
                showAlert('Status updated successfully!', 'success');
            } else {
                showAlert('Failed to update status!', 'danger');
            }
        }.bind(this),
        error: function () {
            showAlert('An error occurred!', 'danger');
        }
    });
});

function showAlert(message, type) {
    var alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;

    $('.container-fluid').prepend(alertHtml);

    // Auto remove after 3 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);
}
</script>

<script>
$(document).ready(function () {
    // Initialize DataTable
    var table = $('#leaveTable').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>tip',
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "",
            searchPlaceholder: "Search leaves...",
            lengthMenu: "_MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)"
        },
        columnDefs: [
            { orderable: false, targets: [0, 7] }
        ],
        order: [[2, 'desc']]
    });

    // Update custom info
    function updateCustomInfo() {
        var info = table.page.info();
        var start = (info.recordsDisplay === 0) ? 0 : info.start + 1;
        var end = info.end;
        var total = info.recordsDisplay;
        $('#dt-custom-info').text('Showing ' + start + ' to ' + end + ' of ' + total + ' entries');
    }
    updateCustomInfo();
    table.on('draw', updateCustomInfo);

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Select all functionality
    $('#select-all, #select-all-main').on('change', function () {
        var checked = $(this).prop('checked');
        $('.leave-checkbox').prop('checked', checked).trigger('change');
    });

    // Toggle bulk actions based on checkbox selection
    function toggleBulkAction() {
        let anyChecked = $('.leave-checkbox:checked').length > 0;

        if (anyChecked) {
            $('#bulk-action, #bulk-action-main').show().prop('disabled', false);
            $('#apply-action').show();
            $('#bulkDeleteBtn').prop('disabled', false);
        } else {
            $('#bulk-action, #bulk-action-main').hide().prop('disabled', true).val('');
            $('#status-dropdown').hide().prop('disabled', true).val('');
            $('#apply-action').hide();
            $('#bulkDeleteBtn').prop('disabled', true);
        }
    }

    // When row checkboxes change
    $(document).on('change', '.leave-checkbox', function () {
        // Toggle bulk actions
        toggleBulkAction();

        // Sync the select-all checkbox
        var totalCheckboxes = $('.leave-checkbox').length;
        var checkedCount = $('.leave-checkbox:checked').length;
        $('#select-all, #select-all-main').prop('checked', totalCheckboxes === checkedCount);
    });

    // Bulk action dropdown change
    $(document).on('change', '#bulk-action', function () {
        if ($(this).val() === 'change_status') {
            $('#status-dropdown').show().prop('disabled', false);
        } else {
            $('#status-dropdown').hide().prop('disabled', true).val('');
        }
    });

    // Apply bulk action
    $('#apply-action').on('click', function () {
        let action = $('#bulk-action').val();
        let status = $('#status-dropdown').val();
        let selected = $('.leave-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (selected.length === 0) {
            showAlert('Please select at least one leave.', 'warning');
            return;
        }

        if (action === '') {
            showAlert('Please select an action.', 'warning');
            return;
        }

        if (action === 'change_status' && !status) {
            showAlert('Please select a status.', 'warning');
            return;
        }

        let confirmMessage = action === 'delete'
            ? `Are you sure you want to delete ${selected.length} selected leave(s)? This action cannot be undone.`
            : `Are you sure you want to update status for ${selected.length} selected leave(s)?`;

        if (!confirm(confirmMessage)) return;

        // Disable button during processing
        $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Processing...');

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
                showAlert(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'An error occurred';
                showAlert(msg, 'danger');
                $('#apply-action').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Apply');
            }
        });
    });

    // Bulk delete
    $('#bulkDeleteBtn').on('click', function (e) {
        e.preventDefault();

        var selected = $('.leave-checkbox:checked').map(function () { return $(this).val(); }).get();

        if (selected.length === 0) {
            showAlert('Please select at least one leave.', 'warning');
            return;
        }

        if (!confirm(`Are you sure you want to delete ${selected.length} selected leave(s)? This action cannot be undone.`)) {
            return;
        }

        // Disable button during processing
        $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Deleting...');

        $.ajax({
            url: "{{ route('leaves.bulk-delete') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                ids: selected
            },
            success: function (res) {
                if (res.success ?? true) {
                    showAlert(res.message ?? 'Deleted successfully', 'success');
                    // Remove rows from DataTable
                    table.rows( $('.leave-checkbox:checked').closest('tr') ).remove().draw();
                    toggleBulkAction();
                } else {
                    showAlert(res.message || 'Something went wrong', 'danger');
                }
                $('#bulkDeleteBtn').prop('disabled', false).html('<i class="bi bi-trash me-1"></i>Delete');
            },
            error: function (xhr) {
                var msg = 'Error: ' + (xhr.responseJSON?.message || xhr.statusText);
                showAlert(msg, 'danger');
                $('#bulkDeleteBtn').prop('disabled', false).html('<i class="bi bi-trash me-1"></i>Delete');
            }
        });
    });

    // Initial toggle
    toggleBulkAction();
});
</script>
@endpush

@endsection
