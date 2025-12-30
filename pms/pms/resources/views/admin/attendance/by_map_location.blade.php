@extends('admin.layout.app')

@section('title', 'Employee Attendance')

@section('content')
<div class="container">

    <h4 class="mb-3">📍 Attendance by Location ({{ $date }})</h4>

    {{-- Admin filters --}}
    @if(auth()->user()->role === 'admin')
    <div class="row mb-3">
        <div class="col-md-3">
            <select id="employeeFilter" class="form-select form-select-sm">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp['id'] }}">{{ $emp['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select id="departmentFilter" class="form-select form-select-sm">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select id="lateFilter" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="1">Late</option>
                <option value="0">On Time</option>
            </select>
        </div>

        <div class="col-md-3">
            <button id="resetFilters" class="btn btn-sm btn-secondary">Reset</button>
        </div>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 me-2">
            <i class="bi bi-geo-alt text-primary"></i> Attendance by Location
        </h4>

        <div class="btn-group" role="group">
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary" title="Summary">
                <i class="bi bi-list-ul"></i>
            </a>
            <a href="{{ route('attendance.byMember') }}" class="btn btn-secondary" title="Attendance by Member">
                <i class="bi bi-person"></i>
            </a>
            <a href="{{ route('attendance.byHour') }}" class="btn btn-secondary" title="Attendance by Hour">
                <i class="bi bi-clock"></i>
            </a>
            <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="btn btn-primary" title="Attendance by Location">
                <i class="bi bi-geo-alt"></i>
            </a>
        </div>
    </div>

    {{-- Map container --}}
    <div id="map" style="height: 600px; border-radius: 10px;"></div>

    {{-- Debug / summary --}}
    <div class="mt-3">
        <strong>Attendance rows for {{ $date }}:</strong>
        Plotted: <span id="attendanceCount">{{ count($attendanceData ?? []) }}</span>
        &nbsp;/&nbsp;
        Total: <span id="attendanceTotal">{{ count($debugData ?? []) }}</span>

        <ul id="attendanceDebugList" style="max-height:220px; overflow:auto; padding-left:18px; margin-top:10px;">
            @foreach($debugData ?? [] as $att)
                <li style="margin-bottom:6px;">
                    <strong>{{ data_get($att, 'user.name', 'Unknown') }}</strong>
                    &nbsp;—&nbsp;
                    Lat: {{ data_get($att, 'latitude', '-') ?: '-' }},
                    Lng: {{ data_get($att, 'longitude', '-') ?: '-' }},
                    &nbsp;—&nbsp;
                    Late: {{ data_get($att, 'late', false) ? 'Yes' : 'No' }}
                    &nbsp;(<small>att_id: {{ data_get($att, 'attendance_id') }}, user_id: {{ data_get($att, 'user.id', '-') }}</small>)
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // data passed from controller
    var attendanceData = @json($attendanceData ?? []);
    var debugData = @json($debugData ?? []);
    var defaultAvatar = "{{ asset('default-avatar.png') }}";

    console.log('attendanceData (mapPoints):', attendanceData);
    console.log('debugData (all rows):', debugData);

    // initialize map
    var defaultCenter = [22.5726, 88.3639]; // Kolkata fallback
    var map = L.map('map').setView(defaultCenter, 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var markers = [];

    function clearMarkers() {
        markers.forEach(function(m) { map.removeLayer(m); });
        markers = [];
    }

    function addMarker(att) {
        if (!att) return null;

        var user = att.user || { id: null, name: 'Unknown', avatar: null };
        var avatar = user.avatar ? user.avatar : defaultAvatar;
        var lat = parseFloat(att.latitude);
        var lng = parseFloat(att.longitude);

        if (isNaN(lat) || isNaN(lng)) return null;

        var popup = `
            <div style="text-align:center;">
                <img src="${avatar}" style="width:50px;height:50px;border-radius:50%;margin-bottom:6px;">
                <br><strong>${user.name}</strong><br>
                <small>${att.designation ?? ''}</small><br>
                <small>Dept: ${att.department ? (att.department.name ?? '-') : '-'}</small><br>
                <small>${att.late ? '⏰ Late' : '✅ On Time'}</small>
            </div>
        `;

        var marker = L.marker([lat, lng]).addTo(map).bindPopup(popup);
        markers.push(marker);
        return marker;
    }

    function renderMarkers() {
        clearMarkers();

        var empFilterEl = document.getElementById('employeeFilter');
        var deptFilterEl = document.getElementById('departmentFilter');
        var lateFilterEl = document.getElementById('lateFilter');

        var empFilter = empFilterEl ? empFilterEl.value : '';
        var deptFilter = deptFilterEl ? deptFilterEl.value : '';
        var lateFilter = lateFilterEl ? lateFilterEl.value : '';

        attendanceData.forEach(function(att) {
            // employee filter
            if (empFilter) {
                var uid = att.user && att.user.id ? String(att.user.id) : '';
                if (uid !== String(empFilter)) return;
            }

            // department filter
            if (deptFilter) {
                var did = att.department && att.department.id ? String(att.department.id) : '';
                if (did !== String(deptFilter)) return;
            }

            // late filter
            if (lateFilter !== "") {
                var wantLate = lateFilter === "1";
                var actualLate = Boolean(att.late);
                if (actualLate !== wantLate) return;
            }

            addMarker(att);
        });

        // fit to markers if any
        if (markers.length > 0) {
            var group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.2));
        } else {
            map.setView(defaultCenter, 6);
        }

        // update plotted count (and keep total visible)
        document.getElementById('attendanceCount').textContent = markers.length;
    }

    // initial render
    renderMarkers();

    // attach events if elements exist
    document.getElementById('employeeFilter')?.addEventListener('change', renderMarkers);
    document.getElementById('departmentFilter')?.addEventListener('change', renderMarkers);
    document.getElementById('lateFilter')?.addEventListener('change', renderMarkers);

    document.getElementById('resetFilters')?.addEventListener('click', function () {
        if (document.getElementById('employeeFilter')) document.getElementById('employeeFilter').value = "";
        if (document.getElementById('departmentFilter')) document.getElementById('departmentFilter').value = "";
        if (document.getElementById('lateFilter')) document.getElementById('lateFilter').value = "";
        renderMarkers();
    });
});
</script>
@endpush
