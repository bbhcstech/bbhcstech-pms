@extends('admin.layout.app')

@section('title', 'Leave Calendar')

@section('content')
<main class="main">
    <div class="container py-4">
        <h4 class="fw-bold mb-0 me-3">Leaves</h4>
        
        <div class="card mb-3">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <!-- Employee -->
                        <div class="col-md-4">
                        <label class="form-label fw-bold">Employee</label>
                        <select id="employee" name="employee" class="form-control select2">
                            <option value="">Select Employee</option>
                            @foreach($employee_data as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

            
                        <!-- Leave Type -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Leave Type</label>
                            <select id="leave_type" name="leave_type" class="form-select">
                                <option value="">All</option>
                                <option value="casual">Casual</option>
                                <option value="sick">Sick</option>
                                <option value="leave_without_pay">Leave Without Pay</option>
                                <!-- Add more dynamically if needed -->
                            </select>
                        </div>
            
                        <!-- Status -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">All</option>
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- Left Side -->
            <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> &nbsp;New Leave
            </a>

            <!-- Right Side: Switcher -->
            <div class="btn-group" role="group" aria-label="Leave View Switcher">
                
                <a href="{{ route('leaves.index') }}" 
                   class="btn btn-sm btn-outline-primary {{ request()->routeIs('leaves.index') ? 'active' : '' }}" 
                   data-bs-toggle="tooltip" title="Table View">
                    <i class="bi bi-list-ul"></i>
                </a>
                <a href="{{ route('leaves.calendar') }}" 
                   class="btn btn-sm btn-outline-primary {{ request()->routeIs('leaves.calendar') ? 'active' : '' }}" 
                   data-bs-toggle="tooltip" title="Calendar">
                    <i class="bi bi-calendar"></i>
                </a>
                
                <!--<a href="" -->
                <!--   class="btn btn-sm btn-outline-primary {{ request()->routeIs('leaves.personal') ? 'active' : '' }}" -->
                <!--   data-bs-toggle="tooltip" title="My Leaves">-->
                <!--    <i class="bi bi-person"></i>-->
                <!--</a>-->
            </div>
        </div>

        <div id='calendar'></div>
    </div>
</main>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    $('#employee').select2({
        placeholder: "Start typing to search...",
        allowClear: true,
        width: '100%'
    });

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 600,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        events: {
            url: "{{ route('leaves.calendar.data') }}",
            method: 'GET',
            extraParams: function () {
                return {
                    employee: $('#employee').val(),
                    leave_type: $('#leave_type').val(),
                    status: $('#status').val()
                };
            }
        },
        eventTextColor: '#fff'
    });

    calendar.render();

    // Refetch events on filter change
    $('#filterForm select').on('change', function () {
        calendar.refetchEvents();
    });

    // Clear filters
    $('<button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearFilters">Clear Filters</button>')
        .insertAfter('#filterForm')
        .on('click', function() {
            $('#filterForm select').val(null).trigger('change');
            calendar.refetchEvents();
        });
});

</script>
@endsection


