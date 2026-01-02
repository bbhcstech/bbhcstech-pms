@extends('admin.layout.app')

@section('title', 'Leave Calendar')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-2">Leave Calendar</h1>
            <p class="text-muted mb-0">Visual overview of all leave requests</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>New Leave Request
            </a>
        </div>
    </div>

    <!-- View Toggle -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}" class="text-decoration-none">Leaves</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Calendar View</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('leaves.index') }}"
                       class="btn btn-outline-primary {{ request()->routeIs('leaves.index') ? 'active' : '' }}"
                       data-bs-toggle="tooltip" title="Table View">
                        <i class="bi bi-list-ul me-1"></i>Table
                    </a>
                    <a href="{{ route('leaves.calendar') }}"
                       class="btn btn-outline-primary {{ request()->routeIs('leaves.calendar') ? 'active' : '' }}"
                       data-bs-toggle="tooltip" title="Calendar View">
                        <i class="bi bi-calendar me-1"></i>Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent py-3">
            <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Filter Calendar</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <!-- Employee Filter -->
                <div class="col-lg-3 col-md-6">
                    <label for="employee" class="form-label">
                        Employee
                        <i class="bi bi-info-circle text-muted ms-1"
                           data-bs-toggle="tooltip"
                           title="Filter by specific employee"></i>
                    </label>
                    <select id="employee" name="employee" class="form-select select2">
                        <option value="">All Employees</option>
                        @foreach($employee_data as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Leave Type Filter -->
                <div class="col-lg-3 col-md-6">
                    <label for="leave_type" class="form-label">
                        Leave Type
                    </label>
                    <select id="leave_type" name="leave_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="casual">Casual Leave</option>
                        <option value="sick">Sick Leave</option>
                        <option value="leave_without_pay">Leave Without Pay</option>
                        <option value="half_day">Half Day</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-lg-3 col-md-6">
                    <label for="status" class="form-label">
                        Status
                    </label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-lg-3 col-md-6 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" id="applyFilters" class="btn btn-primary flex-fill">
                            <i class="bi bi-filter me-2"></i>Apply Filters
                        </button>
                        <button type="button" id="clearFilters" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Calendar Legend -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="text-muted me-2"><strong>Legend:</strong></span>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success bg-opacity-10 text-success me-1 px-2 py-1 rounded">
                        <i class="bi bi-square-fill text-success me-1"></i>Approved
                    </span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning me-1 px-2 py-1 rounded">
                        <i class="bi bi-square-fill text-warning me-1"></i>Pending
                    </span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-danger bg-opacity-10 text-danger me-1 px-2 py-1 rounded">
                        <i class="bi bi-square-fill text-danger me-1"></i>Rejected
                    </span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-info bg-opacity-10 text-info me-1 px-2 py-1 rounded">
                        <i class="bi bi-square-fill text-info me-1"></i>Multiple Days
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div id="calendar" class="p-3"></div>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-success bg-opacity-10 border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0" id="approvedCount">0</h5>
                            <span class="text-muted small">Approved Leaves</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning bg-opacity-10 border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-fill text-warning fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0" id="pendingCount">0</h5>
                            <span class="text-muted small">Pending Leaves</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger bg-opacity-10 border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-x-circle-fill text-danger fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0" id="rejectedCount">0</h5>
                            <span class="text-muted small">Rejected Leaves</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info bg-opacity-10 border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-week-fill text-info fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0" id="totalCount">0</h5>
                            <span class="text-muted small">Total This Month</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .fc {
        font-family: inherit;
    }
    .fc .fc-toolbar-title {
        font-size: 1.5rem;
        font-weight: 600;
    }
    .fc .fc-button {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        font-weight: 500;
        padding: 0.375rem 0.75rem;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .fc .fc-button:hover {
        background-color: #e9ecef;
    }
    .fc-event {
        border: none;
        border-radius: 4px;
        padding: 2px 4px;
        font-size: 0.85rem;
        cursor: pointer;
    }
    .fc-daygrid-day-frame {
        min-height: 100px;
    }
    .fc-daygrid-event {
        margin: 1px 0;
    }
    .fc-daygrid-day-number {
        font-weight: 500;
        padding: 4px !important;
    }
    .fc-today {
        background-color: rgba(13, 110, 253, 0.05) !important;
    }
    .card {
        border-radius: 0.5rem;
    }
    .badge.bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #dee2e6;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Select2
    $('#employee').select2({
        placeholder: "Search employee...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#employee').parent()
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Calendar initialization
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 700,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        initialDate: new Date(),
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        events: {
            url: "{{ route('leaves.calendar.data') }}",
            method: 'GET',
            extraParams: function () {
                return {
                    employee: $('#employee').val(),
                    leave_type: $('#leave_type').val(),
                    status: $('#status').val()
                };
            },
            success: function(events) {
                updateStatistics(events);
            },
            failure: function() {
                console.error('Failed to fetch calendar events');
            }
        },
        eventClick: function(info) {
            // Show leave details when event is clicked
            showLeaveDetails(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltip to events
            info.el.setAttribute('data-bs-toggle', 'tooltip');
            info.el.setAttribute('title', info.event.title);
            info.el.setAttribute('data-bs-html', 'true');

            // Initialize tooltip for this event
            new bootstrap.Tooltip(info.el);
        },
        datesSet: function(info) {
            // Update statistics based on current view
            fetchEventsForStatistics(info.start, info.end);
        }
    });

    calendar.render();

    // Function to show leave details
    function showLeaveDetails(event) {
        const eventData = event.extendedProps;
        const modalHtml = `
            <div class="modal fade" id="leaveDetailModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Leave Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Employee:</label>
                                    <p class="fw-semibold">${event.title}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status:</label>
                                    <p><span class="badge bg-${getStatusColor(eventData.status)}">${eventData.status}</span></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Leave Type:</label>
                                    <p class="fw-semibold">${eventData.type || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Duration:</label>
                                    <p class="fw-semibold">${eventData.duration || 'Full Day'}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Reason:</label>
                                    <p>${eventData.reason || 'No reason provided'}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Start Date:</label>
                                    <p class="fw-semibold">${event.start.toLocaleDateString()}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Date:</label>
                                    <p class="fw-semibold">${event.end ? event.end.toLocaleDateString() : event.start.toLocaleDateString()}</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="${eventData.editUrl || '#'}" class="btn btn-primary">Edit Leave</a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('leaveDetailModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add new modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('leaveDetailModal'));
        modal.show();
    }

    // Function to get status color
    function getStatusColor(status) {
        switch(status) {
            case 'approved': return 'success';
            case 'pending': return 'warning';
            case 'rejected': return 'danger';
            default: return 'secondary';
        }
    }

    // Function to update statistics
    function updateStatistics(events) {
        const counts = {
            approved: 0,
            pending: 0,
            rejected: 0,
            total: events.length
        };

        events.forEach(event => {
            if (event.extendedProps && event.extendedProps.status) {
                const status = event.extendedProps.status.toLowerCase();
                if (counts.hasOwnProperty(status)) {
                    counts[status]++;
                }
            }
        });

        // Update DOM elements
        document.getElementById('approvedCount').textContent = counts.approded;
        document.getElementById('pendingCount').textContent = counts.pending;
        document.getElementById('rejectedCount').textContent = counts.rejected;
        document.getElementById('totalCount').textContent = counts.total;
    }

    // Function to fetch events for statistics
    function fetchEventsForStatistics(start, end) {
        $.ajax({
            url: "{{ route('leaves.calendar.data') }}",
            method: 'GET',
            data: {
                employee: $('#employee').val(),
                leave_type: $('#leave_type').val(),
                status: $('#status').val(),
                start: start.toISOString(),
                end: end.toISOString()
            },
            success: function(events) {
                updateStatistics(events);
            }
        });
    }

    // Apply filters button
    document.getElementById('applyFilters').addEventListener('click', function() {
        calendar.refetchEvents();
    });

    // Clear filters button
    document.getElementById('clearFilters').addEventListener('click', function() {
        $('#employee').val(null).trigger('change');
        $('#leave_type').val('');
        $('#status').val('');
        calendar.refetchEvents();
    });

    // Filter change listeners
    $('#employee, #leave_type, #status').on('change', function() {
        calendar.refetchEvents();
    });

    // Initial statistics update
    fetchEventsForStatistics(
        calendar.view.activeStart,
        calendar.view.activeEnd
    );
});
</script>
@endsection
