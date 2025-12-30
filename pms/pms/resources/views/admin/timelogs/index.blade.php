@extends('admin.layout.app')

@section('content')
<div class="container">
    <br>

    @if($project)
        <a href="{{ route('projects.index') }}" class="btn btn-secondary mb-3">← Back to Projects</a>

        {{-- Sub-navigation bar --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item"><a class="nav-link active" href="{{ route('projects.show', $project->id) }}">Overview</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('project-members.index', $project->id)}}">Members</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('project-files.index', $project->id)}}">Files</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('milestones.index', $project->id)}}">Milestones</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('projects.tasks.index', $project->id) }}">Tasks</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('projects.tasks.board', $project->id) }}">Task Board</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('projects.gantt', $project->id) }}">Gantt Chart</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('projects.timelogs.index', $project->id) }}">Timesheet</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('expenses.index', $project->id) }}">Expenses</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('projects.notes.index', $project->id) }}">Notes</a></li>
            <li class="nav-item"><a class="nav-link text-primary" href="#" id="toggle-more">More ▾</a></li>
        </ul>

        <ul class="nav nav-tabs mb-4 d-none" id="more-tabs">
            <li class="nav-item"><a class="nav-link" href="{{ route('projects.discussions.index', $project->id) }}" >Discussion</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('projects.burndown', $project->id) }}">Burndown Chart</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.activities.project', $project->id) }}">Activity</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('tickets.index', ['project_id' => $project->id]) }}">Ticket</a></li>
        </ul>
    @endif

    <h4 class="mb-0 me-3">Timesheet</h4>
    &nbsp;

    <form method="GET" action="{{ route('timelogs.index') }}" class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Duration</label>
            <input type="text" name="daterange" id="daterange" class="form-control"
                   value="{{ request('start_date') && request('end_date') ? request('start_date').' To '.request('end_date') : '' }}">
            <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Employee</label>
            <select name="user_id" class="form-select select2">
                @if(auth()->user()->role === 'admin')
                    <option value="">All</option>
                @endif

                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}"
                        @if(auth()->user()->role !== 'admin')
                            selected
                        @elseif(request('user_id') == $emp->id)
                            selected
                        @endif
                    >
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="{{ route('timelogs.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    &nbsp;

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <a href="{{ route('timelogs.create') }}" class="btn btn-primary">Log Time</a>
        </div>

        <div class="btn-group" role="group">
            <div class="d-flex mb-3">
                <select id="bulkLogStatus" class="form-select" disabled>
                    <option value="">Change Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <button id="applyBulkLogStatus" class="btn btn-primary" disabled>Apply</button>
            </div>
            &nbsp;&nbsp;
            <div class="d-flex align-items-center mb-3">
                <a href="{{ route('timelogs.index') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.index') ? 'active' : '' }}" data-toggle="tooltip" title="Timesheet">
                    <i class="side-icon bi bi-list-ul"></i>
                </a>

                <a href="{{ route('timelogs.calendar') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.calendar') ? 'active' : '' }}"  data-toggle="tooltip" title="Calendar">
                    <i class="side-icon bi bi-calendar"></i>
                </a>

                <a href="{{ route('timelogs.byEmployee')}}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.byEmployee') ? 'active' : '' }}" data-toggle="tooltip" title="Employee TimeLogs">
                    <i class="side-icon bi bi-person"></i>
                </a>

                <a href="javascript:;" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#howItWorksModal" data-bs-toggle="tooltip" title="How It Works">
                    <i class="side-icon bi bi-question-circle"></i>
                </a>
            </div>
        </div>
    </div>
    &nbsp;

    <!-- Modal -->
    <div class="modal fade" id="howItWorksModal" tabindex="-1" aria-labelledby="howItWorksLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="howItWorksLabel">Timesheet Lifecycle</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <img src="{{ asset('timesheet-lifecycle.png') }}" alt="Timesheet Lifecycle" class="img-fluid">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table id="timelogTable" class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th><input type="checkbox" id="selectAllLogs"></th>
                <th>Id</th>
                <th>Code</th>
                <th>Task</th>
                <th>Employee</th>
                <th style="white-space: nowrap;">Start Time</th>
                <th style="white-space: nowrap;">End Time</th>
                <th style="white-space: nowrap;">Total Hours</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $key => $log)
            <tr data-id="{{ $log->id }}">
                <td><input type="checkbox" class="log-checkbox" value="{{ $log->id }}"></td>
                <td>{{ $key + 1 }}</td>

                {{-- ✅ AUTO-GENERATED CODE --}}
                <td>
                    @php
                        $prefix = $log->project->project_code ?? '';
                        $autoNumber = str_pad($log->id, 4, '0', STR_PAD_LEFT);
                        echo $prefix . $autoNumber;
                    @endphp
                </td>

                <td>{{ $log->task->title ?? '-' }}</td>
                <td>{{ $log->user->name ?? '-' }}</td>
                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($log->start_date)->format('d-m-Y') }} {{ \Carbon\Carbon::parse($log->start_time)->format('h:i A') }}</td>
                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($log->end_date)->format('d-m-Y') }} {{ \Carbon\Carbon::parse($log->end_time)->format('h:i A') }}</td>
                <td>{{ is_numeric($log->total_hours) ? number_format($log->total_hours, 2) : ($log->total_hours ?? '0h 0m 0s') }}</td>
                <td class="statusCell">{{ ucfirst($log->status ?? 'pending') }}</td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>

                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('timelogs.show', $log->id) }}"><i class="bi bi-eye me-2"></i> View</a></li>
                            <li><a class="dropdown-item" href="{{ route('timelogs.edit', $log->id) }}"><i class="bi bi-pencil-square me-2"></i> Edit</a></li>
                            <li>
                                <form action="{{ route('timelogs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this log?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-trash me-2"></i> Delete
                                    </button>
                                </form>
                            </li>
                        </ul>

                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

@push('js')
<script>
$(document).ready(function () {
    $('#timelogTable').DataTable({
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

    // put Bulk Delete directly under "Showing X to Y of Z entries"
    const info = $('#timelogTable_info');
    if (info.length && !$('#bulkDeleteBtn').length) {
        info.parent().append(
            '<div class="mt-2">' +
                '<button id="bulkDeleteBtn" class="btn btn-danger btn-sm" disabled>Bulk Delete</button>' +
            '</div>'
        );
    }
});
</script>

<script>
document.getElementById('toggle-more').addEventListener('click', function(e) {
    e.preventDefault();
    const moreTabs = document.getElementById('more-tabs');
    if (moreTabs.classList.contains('d-none')) {
        moreTabs.classList.remove('d-none');
        this.innerHTML = 'Less ▴';
    } else {
        moreTabs.classList.add('d-none');
        this.innerHTML = 'More ▾';
    }
});
</script>

<script>
$(function() {
    $('#daterange').daterangepicker({
        autoUpdateInput: false,
        locale: { cancelLabel: 'Clear' },
        ranges: {
            'Today': [moment(), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
            'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment()],
            'Last 1 Year': [moment().subtract(1, 'year').startOf('day'), moment()],
            'Custom Range': []
        }
    });

    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
        $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' To ' + picker.endDate.format('DD-MM-YYYY'));
    });

    $('#daterange').on('cancel.daterangepicker', function() {
        $(this).val('');
        $('#start_date').val('');
        $('#end_date').val('');
    });
});
</script>

<script>
$(document).ready(function() {
    function refreshBulkControls() {
        let selectedCount = $('.log-checkbox:checked').length;
        let statusSelected = $('#bulkLogStatus').val() && $('#bulkLogStatus').val().length > 0;
        $('#bulkLogStatus').prop('disabled', selectedCount === 0);
        $('#applyBulkLogStatus').prop('disabled', selectedCount === 0 || !statusSelected);

        // enable / disable bulk delete
        $('#bulkDeleteBtn').prop('disabled', selectedCount === 0);
    }

    $('#timelogTable tbody').on('change', '.log-checkbox', function () {
        refreshBulkControls();
    });

    $('#selectAllLogs').on('change', function () {
        const checked = $(this).prop('checked');
        $('.log-checkbox').prop('checked', checked).trigger('change');
    });

    $('#bulkLogStatus').on('change', function() {
        refreshBulkControls();
    });

    function normalizeStatus(value) {
        if (!value) return '';
        const v = value.toLowerCase();
        if (v === 'approve' || v === 'approved') return 'approved';
        if (v === 'reject' || v === 'rejected') return 'rejected';
        return 'pending';
    }

    $('#applyBulkLogStatus').on('click', function() {
        let selectedIds = $('.log-checkbox:checked').map(function() { return $(this).val(); }).get();
        let statusRaw = $('#bulkLogStatus').val();
        let status = normalizeStatus(statusRaw);

        if(!status || selectedIds.length === 0){
            alert('Select logs and status to apply.');
            return;
        }

        if (!confirm(`Apply status "${status}" to ${selectedIds.length} selected logs?`)) return;

        $.ajax({
            url: "{{ route('timelogs.bulkStatusUpdate') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                ids: selectedIds,
                status: status
            },
            success: function(res) {
                if (res.success) {

                    selectedIds.forEach(function(id) {
                        const row = $("tr[data-id='" + id + "']");
                        if (row.length) {
                            row.find('.statusCell').text(status.charAt(0).toUpperCase() + status.slice(1));
                        }
                    });

                    $('#selectAllLogs').prop('checked', false);
                    $('.log-checkbox').prop('checked', false);
                    $('#bulkLogStatus').val('');
                    refreshBulkControls();

                    alert(res.message || 'Status updated successfully.');
                } else {
                    alert(res.message || 'Failed to update status.');
                }
            },
            error: function(xhr){
                console.error(xhr);
                let msg = 'Something went wrong!';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });

    // bulk delete click
    $(document).on('click', '#bulkDeleteBtn', function () {
        let selectedIds = $('.log-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) {
            alert('Select at least one log to delete.');
            return;
        }

        if (!confirm(`Delete ${selectedIds.length} selected logs?`)) return;

        $.ajax({
            url: "{{ route('timelogs.bulk-delete') }}",
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}",
                ids: selectedIds
            },
            success: function(res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'Failed to delete logs.');
                }
            },
            error: function(xhr){
                console.error(xhr);
                let msg = 'Something went wrong!';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });
});
</script>
@endpush

@endsection
