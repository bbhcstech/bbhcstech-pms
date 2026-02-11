@extends('admin.layout.app')
@section('title', 'Holiday List')
@section('content')
<main class="main">
    <div class="container py-4">
        <!-- Employee can see title only -->
        <h4 class="fw-bold mb-3">Holiday List</h4>

        <!-- Filter Section (read-only for employee) -->
        <form method="GET" action="{{ route('holidays.employeeView') }}" class="row g-2 mb-3">
            <!-- Month Filter -->
            <div class="col-md-3">
                <select name="month" class="form-select">
                    <option value="">Select Month</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Year Filter -->
            <div class="col-md-3">
                <select name="year" class="form-select">
                    <option value="">Select Year</option>
                    @foreach(range(date('Y')-5, date('Y')+2) as $y)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('holidays.employeeView') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <!-- View Switcher for Employee -->
        <div class="d-flex justify-content-end align-items-center mb-3">
            <div class="btn-group" role="group" aria-label="Holiday View Switcher">
                <a href="{{ route('holidays.calendar') }}"
                   class="btn btn-sm btn-outline-primary {{ request()->routeIs('holidays.calendar') ? 'active' : '' }}"
                   data-toggle="tooltip" title="Calendar View">
                    <i class="bi bi-calendar"></i> Calendar
                </a>
                <a href="{{ route('holidays.employeeView') }}"
                   class="btn btn-sm btn-outline-primary {{ request()->routeIs('holidays.employeeView') ? 'active' : '' }}"
                   data-toggle="tooltip" title="List View">
                    <i class="bi bi-list-ul"></i> List
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="background-color: #28a745; color: white; border-color: #28a745;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Holiday Table (read-only for employee) -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="holidayTable" class="table table-bordered table-hover table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Title</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($holiday->date)->format('d M, Y') }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ \Carbon\Carbon::parse($holiday->date)->format('D') }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar-check text-primary"></i>
                                        {{ $holiday->title }}
                                    </td>
                                    <td>
                                        @if($holiday->type === 'weekly_holiday')
                                            <span class="badge bg-success">Weekly Holiday</span>
                                        @else
                                            <span class="badge bg-primary">Special Holiday</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        <i class="bi bi-calendar-x fs-4"></i>
                                        <p class="mt-2 mb-0">No holidays found for the selected period.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

@push('js')
<script>
    $(document).ready(function () {
        $('#holidayTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            responsive: true,
            pageLength: 10,
            lengthMenu: [10,25,50,100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search holidays..."
            },
            order: [[0, 'asc']]
        });
    });
</script>
@endpush
@endsection
