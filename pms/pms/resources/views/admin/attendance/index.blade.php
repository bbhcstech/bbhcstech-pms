@extends('admin.layout.app')

@section('title', 'Employee Attendance')

@section('content')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<main class="main">
    <div class="content-wrapper py-4 px-3" style="background-color: #f5f7fa; min-height: 100vh;">
        <div class="container-fluid">

            @php $user = Auth::user(); @endphp

            <div class="d-flex align-items-center mb-3">
                <h4 class="fw-bold mb-0">Attendance</h4>
            </div>

            {{-- FILTER ROW --}}
            <div class="mb-3 border-bottom pb-3">
                <form id="attendanceFilter" class="d-flex flex-wrap align-items-end gap-3">

                    {{-- Employee --}}
                    <div>
                        <label for="user_id" class="form-label">Employee</label>
                        <select name="user_id" id="user_id" class="form-select form-select-sm select2" style="min-width:180px;">
                            @if($user->role === 'admin')
                                <option value="">All</option>
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
                    <div>
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-select form-select-sm select2" style="min-width:160px;">
                            <option value="">All</option>
                            @foreach($departments as $detp)
                                <option value="{{ $detp->id }}" {{ request('department_id') == $detp->id ? 'selected' : '' }}>
                                    {{ $detp->dpt_name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Designation --}}
                    <div>
                        <label for="designation_id" class="form-label">Designation</label>
                        <select name="designation_id" id="designation_id" class="form-select form-select-sm select2" style="min-width:160px;">
                            <option value="">All</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                                    {{ $designation->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Month --}}
                    <div>
                        <label for="month" class="form-label">Month</label>
                        <select name="month" id="month" class="form-select form-select-sm select2" style="min-width:130px;">
                            <option value="">All</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Year --}}
                    <div>
                        <label for="year" class="form-label">Year</label>
                        <select name="year" id="year" class="form-select form-select-sm select2" style="min-width:100px;">
                            <option value="">All</option>
                            @foreach(range(date('Y') - 2, date('Y') + 2) as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2 align-items-center">
                        <button type="submit" class="btn btn-success btn-sm">Filter</button>
                        <a href="{{ route('attendance.index') }}" class="btn btn-secondary btn-sm">Reset</a>

                        <!--<button type="button" onclick="exportPdf()" class="btn btn-danger">-->
                        <!--    <i class="fa fa-file-pdf"></i> Export PDF-->
                        <!--</button>-->
                        
                        <!--<button type="button" onclick="exportExcel()" class="btn btn-success">-->
                        <!--    <i class="fa fa-file-excel"></i> Export Excel-->
                        <!--</button>-->
                    </div>
                </form>
            </div>

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                @if($user->role == 'admin')
                    <a href="{{ route('attendance.create') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle"></i> Add Attendance
                    </a>
                @endif

                <div class="btn-group mt-2 ms-auto" role="group">
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary f-14" title="Summary"><i class="bi bi-list-ul"></i></a>
                    <a href="{{ route('attendance.byMember') }}" class="btn btn-secondary f-14" title="Attendance by Member"><i class="bi bi-person"></i></a>
                    <a href="{{ route('attendance.byHour') }}" class="btn btn-secondary f-14" title="Attendance by Hour"><i class="bi bi-clock"></i></a>
                    <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="btn btn-secondary f-14" title="Attendance by Location"><i class="bi bi-geo-alt"></i></a>
                </div>
            </div>

            {{-- NOTE --}}
            <div class="alert alert-info mt-3" style="font-size:14px;">
                <strong>Note:</strong><br>
                <i class='bx bx-star text-warning'></i> Holiday |
                <i class='bx bx-calendar text-primary'></i> Day Off |
                <i class='bx bx-check text-success'></i> Present |
                <i class='bx bxs-star-half text-warning'></i> Half Day |
                <i class='bx bx-time-five text-warning'></i> Late |
                <i class='bx bx-x text-danger'></i> Absent |
                <i class='bx bxs-plane-take-off text-info'></i> On Leave
            </div>

            {{-- TABLE --}}
            <div id="attendance-table" class="mt-3">
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
</main>
@endsection

@push('styles')
<style>
/* small adjustments to help PDF rendering when using same view (if reused) */
@media print {
    body { -webkit-print-color-adjust: exact; }
}
</style>
@endpush

@push('js')
<script>
function exportPdf() {
    const form = document.getElementById('attendanceFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.location.href = '{{ route("attendance.export.pdf") }}?' + params.toString();
}

function exportExcel() {
    const form = document.getElementById('attendanceFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.location.href = '{{ route("attendance.export.excel") }}?' + params.toString();
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Initialize select2 if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({ width: 'resolve' });
    }

    // DataTable initialization helper (safe)
    function initDataTable() {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') return;

        if ($.fn.DataTable.isDataTable('#attendanceTable')) {
            $('#attendanceTable').DataTable().destroy();
        }

        $('#attendanceTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            responsive: true,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search attendance..."
            }
        });
    }

    // try init once (if table exists)
    initDataTable();

    // AJAX filter handler
    const filterForm = document.getElementById('attendanceFilter');
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const form = this;
        const data = new URLSearchParams(new FormData(form)).toString();

        const target = document.getElementById('attendance-table');
        target.innerHTML = '<p>Loading...</p>';

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
            } else {
                target.innerHTML = '<p class="text-danger">No data returned</p>';
            }
        })
        .catch(err => {
            console.error('Filter error:', err);
            target.innerHTML = '<p class="text-danger">Something went wrong. Please try again.</p>';
        });
    });

    // helper to get current filters as query string
    function getFilterQuery() {
        return new URLSearchParams(new FormData(filterForm)).toString();
    }

    // Export handlers - use url() routes so blade resolves correctly
    document.getElementById('exportExcelBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const qs = getFilterQuery();
        const url = "{{ url('attendance/export/excel') }}";
        window.location.href = qs ? (url + '?' + qs) : url;
    });

    document.getElementById('exportPdfBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const qs = getFilterQuery();
        const url = "{{ url('attendance/export/pdf') }}";
        const full = qs ? (url + '?' + qs) : url;

        // open in new tab/window to ensure download prompt appears (not AJAX)
        const newWin = window.open(full, '_blank');
        if (!newWin) {
            // popup blocked: fallback to navigation
            window.location.href = full;
        }
    });

});
</script>
@endpush
