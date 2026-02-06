@extends('admin.layout.app')

@section('title', 'Employee Attendance Dashboard')

@section('content')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<main class="main">
    <div class="content-wrapper py-4 px-3" style="background: linear-gradient(135deg, #f5f7fa 0%, #f0f4f8 100%); min-height: 100vh;">
        <div class="container-fluid">

            @php $user = Auth::user(); @endphp

            {{-- Page Header --}}
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-2" style="color: #2c3e50;">
                            <i class="fas fa-calendar-check me-2" style="color: #3498db;"></i>
                            Employee Attendance Dashboard
                        </h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">
                            <i class="fas fa-info-circle me-1"></i>
                            Monitor and manage employee attendance records
                        </p>
                    </div>

                    @if($user->role == 'admin')
                    <a href="{{ route('attendance.create') }}" class="btn btn-success btn-hover-lift shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i> Add Attendance
                    </a>
                    @endif
                </div>
            </div>

            {{-- Quick Stats Cards --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-primary shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Employees
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ count($users) }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-success shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Current Month
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ \Carbon\Carbon::createFromDate(null, $month)->format('F') }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-info shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Selected Year
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $year }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-warning shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Days in Month
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $daysInMonth }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Panel --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold" style="color: #2c3e50;">
                        <i class="fas fa-filter me-2"></i>Filter Attendance Records
                    </h6>
                </div>
                <div class="card-body">
                    <form id="attendanceFilter" class="row g-3 align-items-end">
                        {{-- Employee --}}
                        <div class="col-md-2">
                            <label for="user_id" class="form-label small fw-semibold text-muted">Employee</label>
                            <select name="user_id" id="user_id" class="form-select form-select-sm shadow-sm">
                                @if($user->role === 'admin')
                                    <option value="">All Employees</option>
                                    @foreach($users as $detail)
                                        <option value="{{ $detail->id }}" {{ request('user_id') == $detail->id ? 'selected' : '' }}>
                                            {{ $detail->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="{{ $user->id }}" selected>{{ $user->name }}</option>
                                @endif
                            </select>
                        </div>

                        {{-- Department --}}
                        @if($user->role == 'admin')
                        <div class="col-md-2">
                            <label for="department_id" class="form-label small fw-semibold text-muted">Department</label>
                            <select name="department_id" id="department_id" class="form-select form-select-sm shadow-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $detp)
                                    <option value="{{ $detp->id }}" {{ request('department_id') == $detp->id ? 'selected' : '' }}>
                                        {{ $detp->dpt_name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Designation --}}
                        <div class="col-md-2">
                            <label for="designation_id" class="form-label small fw-semibold text-muted">Designation</label>
                            <select name="designation_id" id="designation_id" class="form-select form-select-sm shadow-sm">
                                <option value="">All Designations</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                                        {{ $designation->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Month --}}
                        <div class="col-md-2">
                            <label for="month" class="form-label small fw-semibold text-muted">Month</label>
                            <select name="month" id="month" class="form-select form-select-sm shadow-sm">
                                <option value="">All Months</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Year --}}
                        <div class="col-md-2">
                            <label for="year" class="form-label small fw-semibold text-muted">Year</label>
                            <select name="year" id="year" class="form-select form-select-sm shadow-sm">
                                <option value="">All Years</option>
                                @foreach(range(date('Y') - 2, date('Y') + 2) as $y)
                                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Buttons --}}
                        <div class="col-md-2">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                                    <i class="fas fa-search me-1"></i> Apply Filter
                                </button>
                                <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Navigation Tabs --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-2">
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="{{ route('attendance.index') }}" class="nav-tab-btn active mx-1">
                            <i class="fas fa-list-ul me-2"></i> Summary
                        </a>
                        <a href="{{ route('attendance.byMember') }}" class="nav-tab-btn mx-1">
                            <i class="fas fa-user me-2"></i> By Member
                        </a>
                        <a href="{{ route('attendance.byHour') }}" class="nav-tab-btn mx-1">
                            <i class="fas fa-clock me-2"></i> By Hour
                        </a>
                        <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="nav-tab-btn mx-1">
                            <i class="fas fa-map-marker-alt me-2"></i> Location View
                        </a>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-3">
                    <h6 class="mb-3 fw-semibold" style="color: #2c3e50;">
                        <i class="fas fa-key me-2"></i>Attendance Status Legend
                    </h6>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="legend-item">
                            <span class="legend-icon present"><i class="fas fa-check"></i></span>
                            <span class="legend-text">Present</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-icon absent"><i class="fas fa-times"></i></span>
                            <span class="legend-text">Absent</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-icon late"><i class="fas fa-clock"></i></span>
                            <span class="legend-text">Late</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-icon halfday"><i class="fas fa-star-half-alt"></i></span>
                            <span class="legend-text">Half Day</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-icon holiday"><i class="fas fa-star"></i></span>
                            <span class="legend-text">Holiday</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-icon dayoff"><i class="fas fa-calendar"></i></span>
                            <span class="legend-text">Day Off</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-icon leave"><i class="fas fa-plane-departure"></i></span>
                            <span class="legend-text">On Leave</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Table --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold" style="color: #2c3e50;">
                        <i class="fas fa-table me-2"></i>Attendance Summary
                    </h6>
                    <!-- <div class="btn-group" role="group">
                        <button type="button" onclick="exportExcel()" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                        <button type="button" onclick="exportPdf()" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </button>
                    </div> -->
                </div>
                <div class="card-body p-0">
                    <div id="attendance-table">
                        @include('admin.attendance.table', [
                            'users' => $users,
                            'attendanceMap' => $attendanceMap,
                            'daysInMonth' => $daysInMonth,
                            'month' => $month,
                            'year' => $year
                        ])
                    </div>
                </div>
            </div>

            {{-- Footer Note --}}
            <div class="mt-4 text-center">
                <p class="text-muted small">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Data is updated in real-time. Last updated: {{ now()->format('d M Y, h:i A') }}
                </p>
            </div>

        </div>
    </div>
</main>
@endsection

@push('styles')
<style>
    :root {
        --primary-color: #3498db;
        --success-color: #2ecc71;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
        --info-color: #1abc9c;
        --dark-color: #2c3e50;
        --light-color: #f8f9fa;
    }

    .card {
        border-radius: 12px;
        border: none;
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }

    .btn-hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .nav-tab-btn {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        background: white;
        border-radius: 8px;
        color: var(--dark-color);
        text-decoration: none;
        font-weight: 500;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .nav-tab-btn:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }

    .nav-tab-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .legend-item {
        display: flex;
        align-items: center;
        padding: 6px 12px;
        background: #f8f9fa;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .legend-item:hover {
        background: #e9ecef;
        transform: translateY(-1px);
    }

    .legend-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        margin-right: 8px;
        font-size: 12px;
        color: white;
    }

    .legend-icon.present { background: var(--success-color); }
    .legend-icon.absent { background: var(--danger-color); }
    .legend-icon.late { background: var(--warning-color); }
    .legend-icon.halfday { background: #9b59b6; }
    .legend-icon.holiday { background: #e67e22; }
    .legend-icon.dayoff { background: #3498db; }
    .legend-icon.leave { background: #1abc9c; }

    .legend-text {
        font-size: 14px;
        font-weight: 500;
        color: var(--dark-color);
    }

    .form-select {
        border-radius: 8px;
        border: 1px solid #ddd;
        transition: all 0.3s ease;
    }

    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .page-header {
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }

    .border-left-primary { border-left: 4px solid var(--primary-color) !important; }
    .border-left-success { border-left: 4px solid var(--success-color) !important; }
    .border-left-info { border-left: 4px solid var(--info-color) !important; }
    .border-left-warning { border-left: 4px solid var(--warning-color) !important; }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: var(--dark-color);
        border-top: none;
    }

    .table td {
        vertical-align: middle;
        transition: all 0.2s ease;
    }

    .table tr:hover td {
        background-color: #f8f9fa;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        font-size: 14px;
        color: white;
    }

    @media (max-width: 768px) {
        .nav-tab-btn {
            padding: 8px 12px;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .legend-item {
            margin-bottom: 5px;
        }

        .page-header {
            padding: 15px;
        }
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Initialize select2 if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.form-select').select2({
            width: '100%',
            theme: 'bootstrap-5'
        });
    }

    // DataTable initialization helper (safe)
    function initDataTable() {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') return;

        if ($.fn.DataTable.isDataTable('#attendanceTable')) {
            $('#attendanceTable').DataTable().destroy();
        }

        $('#attendanceTable').DataTable({
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
            responsive: true,
            scrollX: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    previous: "<i class='fas fa-chevron-left'></i>",
                    next: "<i class='fas fa-chevron-right'></i>"
                }
            },
            initComplete: function() {
            }
        });
    }

    // try init once (if table exists)
    initDataTable();

    // AJAX filter handler
    const filterForm = document.getElementById('attendanceFilter');
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Show loading animation
        const target = document.getElementById('attendance-table');
        target.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading attendance data...</p>
            </div>
        `;

        const form = this;
        const data = new URLSearchParams(new FormData(form)).toString();

        fetch("{{ route('attendance.filter') }}?" + data, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(json => {
            if (json.html) {
                target.innerHTML = json.html;
                // re-init datatable if present
                initDataTable();

                // Show success notification
                showNotification('Filters applied successfully!', 'success');
            } else {
                target.innerHTML = '<p class="text-danger text-center py-5">No data returned</p>';
            }
        })
        .catch(err => {
            console.error('Filter error:', err);
            target.innerHTML = '<p class="text-danger text-center py-5">Something went wrong. Please try again.</p>';
        });
    });

    // Notification function
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    // Export functions
    window.exportPdf = function() {
        const form = document.getElementById('attendanceFilter');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        showNotification('Preparing PDF export...', 'info');

        window.location.href = '{{ route("attendance.export.pdf") }}?' + params.toString();
    };

    window.exportExcel = function() {
        const form = document.getElementById('attendanceFilter');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        showNotification('Preparing Excel export...', 'info');

        window.location.href = '{{ route("attendance.export.excel") }}?' + params.toString();
    };

    // Add smooth scrolling to top when filter is applied
    const originalSubmit = filterForm.submit;
    filterForm.submit = function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return originalSubmit.apply(this, arguments);
    };

});
</script>
@endpush
