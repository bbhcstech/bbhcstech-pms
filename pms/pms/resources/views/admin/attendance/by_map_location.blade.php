@extends('admin.layout.app')

@section('title', 'Employee Location Tracking')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Page Header --}}
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-2" style="color: #2c3e50;">
                            <i class="fas fa-map-marker-alt me-2" style="color: #3498db;"></i>
                            Employee Location Tracking
                        </h4>
                        <nav style="--bs-breadcrumb-divider: '›';" aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}" class="text-muted">Attendances</a></li>
                                <li class="breadcrumb-item active" style="color: #3498db;" aria-current="page">Location Tracking</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Employee Search Card --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold" style="color: #2c3e50;">
                        <i class="fas fa-search me-2"></i>Search Employee
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="employeeSearch" class="form-label small fw-semibold text-muted">Select Employee</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <select id="employeeSearch" class="form-select shadow-sm">
                                    <option value="">-- Select Employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp['id'] }}"
                                                data-name="{{ $emp['name'] }}"
                                                data-designation="{{ $emp['designation'] ?? '' }}">
                                            {{ $emp['name'] }} @if(isset($emp['designation'])) - {{ $emp['designation'] }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="dateRange" class="form-label small fw-semibold text-muted">Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" id="dateRange" class="form-control shadow-sm" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button id="searchEmployeeBtn" class="btn btn-primary w-100 shadow-sm">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Selected Employee Info --}}
            <div id="employeeInfoCard" class="card shadow-sm border-0 mb-4" style="display: none;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                         style="width: 60px; height: 60px; font-size: 24px;" id="employeeAvatar">
                                        <!-- Initial will be set by JS -->
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-1 fw-bold" id="employeeName"></h4>
                                    <p class="mb-1 text-muted" id="employeeDesignation"></p>
                                    <div class="d-flex gap-3 mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            Date: <span id="selectedDateRange"></span>
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-history me-1"></i>
                                            Total Records: <span id="totalRecords">0</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-outline-info btn-sm" onclick="showAllOnMap()">
                                    <i class="fas fa-map me-1"></i> Show All on Map
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location Tracking Tabs --}}
            <div id="trackingTabs" class="mb-4" style="display: none;">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="map-tab" data-bs-toggle="tab" data-bs-target="#map-tab-pane" type="button">
                            <i class="fas fa-map me-1"></i> Location Map
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-tab-pane" type="button">
                            <i class="fas fa-list-alt me-1"></i> Detailed Records
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    {{-- Map Tab --}}
                    <div class="tab-pane fade show active" id="map-tab-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-body p-0">
                                <div id="locationMap" style="height: 500px; border-radius: 8px; width: 100%;"></div>
                            </div>
                            <div class="card-footer bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex gap-3">
                                        <div class="d-flex align-items-center">
                                            <div class="legend-marker clock-in me-2"></div>
                                            <small>Clock-In Locations</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="legend-marker clock-out me-2"></div>
                                            <small>Clock-Out Locations</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="legend-marker route me-2"></div>
                                            <small>Movement Route</small>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Click markers for details
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Details Tab --}}
                    <div class="tab-pane fade" id="details-tab-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="py-3 px-4">Date</th>
                                                <th class="py-3 text-center">Clock In</th>
                                                <th class="py-3 text-center">Clock In Location</th>
                                                <th class="py-3 text-center">Clock Out</th>
                                                <th class="py-3 text-center">Clock Out Location</th>
                                                <th class="py-3 text-center">Status</th>
                                                <th class="py-3 text-center">Total Hours</th>
                                                <th class="py-3 text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="attendanceDetails">
                                            <!-- Details will be loaded here -->
                                            <tr>
                                                <td colspan="8" class="text-center py-5 text-muted">
                                                    <i class="fas fa-user-clock fa-2x mb-3"></i>
                                                    <p>Select an employee to view their attendance details</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- No Results Message --}}
            <div id="noResults" class="text-center py-5" style="display: none;">
                <div class="empty-state">
                    <i class="fas fa-user-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Attendance Records Found</h5>
                    <p class="text-muted">The selected employee has no attendance records for the chosen date.</p>
                </div>
            </div>

            {{-- Timeline Modal --}}
            <div class="modal fade" id="timelineModal" tabindex="-1" aria-labelledby="timelineModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="timelineModalLabel">
                                <i class="fas fa-history me-2"></i>Complete Attendance Timeline
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="timelineDetails">
                            <!-- Timeline will be loaded here via AJAX -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3">Loading timeline details...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .page-header {
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }

    .card {
        border-radius: 12px;
        border: none;
    }

    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 12px 24px;
        border-radius: 8px 8px 0 0;
        margin-right: 5px;
    }

    .nav-tabs .nav-link.active {
        color: #3498db;
        background-color: white;
        border-bottom: 3px solid #3498db;
    }

    .nav-tabs .nav-link:hover {
        color: #3498db;
        border-bottom: 3px solid #dee2e6;
    }

    .legend-marker {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .legend-marker.clock-in {
        background-color: #2ecc71;
    }

    .legend-marker.clock-out {
        background-color: #3498db;
    }

    .legend-marker.route {
        background-color: #9b59b6;
    }

    .avatar-placeholder .rounded-circle {
        font-weight: 600;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
        border-top: none;
        border-bottom: 2px solid #e9ecef;
    }

    .table td {
        vertical-align: middle;
        border-color: #f1f3f4;
    }

    .empty-state {
        max-width: 400px;
        margin: 0 auto;
    }

    .timeline-day {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #3498db;
    }

    .timeline-day-header {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e9ecef;
    }

    .timeline-event {
        display: flex;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px dashed #e9ecef;
    }

    .timeline-event:last-child {
        border-bottom: none;
    }

    .event-time {
        min-width: 100px;
        font-weight: 600;
        color: #3498db;
    }

    .event-type {
        min-width: 120px;
    }

    .event-location {
        flex-grow: 1;
        color: #666;
    }

    .badge-location {
        background-color: #e3f2fd;
        color: #1976d2;
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 12px;
    }

    .location-accuracy {
        font-size: 11px;
        color: #95a5a6;
        margin-left: 5px;
    }

    @media (max-width: 768px) {
        #locationMap {
            height: 350px !important;
        }

        .nav-tabs .nav-link {
            padding: 8px 12px;
            font-size: 14px;
        }
    }
</style>
@endpush

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
// Global variables
let employeeMap = null;
let mapMarkers = [];
let mapPolylines = [];
let currentEmployeeId = null;
let currentEmployeeData = null;

document.addEventListener('DOMContentLoaded', function () {
    // Don't initialize map here, wait until needed

    // Search button click
    document.getElementById('searchEmployeeBtn').addEventListener('click', function() {
        searchEmployee();
    });

    // Enter key in employee search
    document.getElementById('employeeSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchEmployee();
        }
    });
});

// Initialize map - ONLY when needed
function initMap(center = [22.5726, 88.3639], zoom = 12) {
    if (employeeMap) {
        employeeMap.remove();
        mapMarkers = [];
        mapPolylines = [];
    }

    // Create map container if it doesn't exist
    const mapContainer = document.getElementById('locationMap');
    if (!mapContainer) {
        console.error('Map container not found');
        return;
    }

    // Ensure map container has proper dimensions
    mapContainer.style.height = '500px';
    mapContainer.style.width = '100%';
    mapContainer.innerHTML = '';

    // Initialize map
    employeeMap = L.map('locationMap').setView(center, zoom);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(employeeMap);

    // Invalidate size to ensure proper rendering
    setTimeout(() => {
        if (employeeMap) {
            employeeMap.invalidateSize();
        }
    }, 100);

    console.log('Map initialized at:', center, 'zoom:', zoom);
}

// Search employee function
function searchEmployee() {
    const employeeSelect = document.getElementById('employeeSearch');
    const employeeId = employeeSelect.value;
    const selectedDate = document.getElementById('dateRange').value;
    const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];

    if (!employeeId) {
        alert('Please select an employee');
        return;
    }

    currentEmployeeId = employeeId;

    // Show loading state
    showLoading(true);

    // Fetch employee attendance data
    fetch('{{ route("attendance.getEmployeeLocations") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_id: employeeId,
            date: selectedDate
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        showLoading(false);

        if (data.success && data.attendance && data.attendance.length > 0) {
            currentEmployeeData = data;
            displayEmployeeInfo(selectedOption, data.attendance.length, selectedDate);
            displayMap(data.attendance);
            displayDetails(data.attendance);

            // Show tracking tabs
            document.getElementById('trackingTabs').style.display = 'block';
            document.getElementById('noResults').style.display = 'none';
        } else {
            // Hide tracking tabs and show no results
            document.getElementById('trackingTabs').style.display = 'none';
            document.getElementById('employeeInfoCard').style.display = 'block';
            document.getElementById('noResults').style.display = 'block';

            // Still show basic employee info
            displayEmployeeInfo(selectedOption, 0, selectedDate);

            // Clear map and tables
            clearMap();
            clearDetails();

            alert('No attendance records found for selected employee');
        }
    })
    .catch(error => {
        showLoading(false);
        console.error('Error:', error);
        alert('Error fetching employee data: ' + error.message);
    });
}

// Display employee information
function displayEmployeeInfo(option, recordCount, date) {
    const employeeName = option.getAttribute('data-name');
    const designation = option.getAttribute('data-designation');
    const avatar = document.getElementById('employeeAvatar');

    // Set avatar initial
    avatar.textContent = employeeName.charAt(0).toUpperCase();

    // Set employee info
    document.getElementById('employeeName').textContent = employeeName;
    document.getElementById('employeeDesignation').textContent = designation || 'N/A';
    document.getElementById('selectedDateRange').textContent = formatDate(date);
    document.getElementById('totalRecords').textContent = recordCount;

    // Show employee info card
    document.getElementById('employeeInfoCard').style.display = 'block';
}

// Display location map
function displayMap(attendanceData) {
    clearMap();

    // Initialize map if not already initialized
    if (!employeeMap) {
        initMap();
    }

    if (!attendanceData || attendanceData.length === 0) {
        employeeMap.setView([22.5726, 88.3639], 12);
        return;
    }

    const locations = [];
    let hasValidLocations = false;

    // Process each attendance record
    attendanceData.forEach((record, index) => {
        const date = record.date;
        const clockInTime = record.clock_in_time;
        const clockOutTime = record.clock_out_time;

        // Add clock-in marker
        if (record.clock_in_latitude && record.clock_in_longitude) {
            const clockInLat = parseFloat(record.clock_in_latitude);
            const clockInLng = parseFloat(record.clock_in_longitude);

            // Validate coordinates
            if (isNaN(clockInLat) || isNaN(clockInLng)) {
                console.warn('Invalid coordinates for clock-in:', record.clock_in_latitude, record.clock_in_longitude);
                return;
            }

            const clockInMarker = L.marker([clockInLat, clockInLng], {
                icon: L.divIcon({
                    html: `<div class="marker-icon clock-in" style="background-color: #2ecc71;"></div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                    className: 'custom-marker'
                })
            }).addTo(employeeMap);

            const popupContent = `
                <div style="min-width: 250px;">
                    <h6 style="margin: 0 0 10px; color: #2c3e50;">
                        <i class="fas fa-sign-in-alt me-2" style="color: #2ecc71;"></i>
                        Clock-In Location
                    </h6>
                    <p style="margin: 5px 0;"><strong>Date:</strong> ${date}</p>
                    <p style="margin: 5px 0;"><strong>Time:</strong> ${clockInTime || 'N/A'}</p>
                    <p style="margin: 5px 0;"><strong>Address:</strong> ${record.clock_in_address || 'Location recorded'}</p>
                    <p style="margin: 5px 0;">
                        <strong>Coordinates:</strong>
                        ${clockInLat.toFixed(6)}, ${clockInLng.toFixed(6)}
                    </p>
                    <div style="text-align: center; margin-top: 10px;">
                        <button class="btn btn-sm btn-outline-primary view-day-btn"
                                onclick="viewDayDetails('${date}')"
                                style="font-size: 11px; padding: 2px 8px;">
                            <i class="fas fa-eye me-1"></i> View Day Details
                        </button>
                    </div>
                </div>
            `;

            clockInMarker.bindPopup(popupContent);
            mapMarkers.push(clockInMarker);
            locations.push([clockInLat, clockInLng]);
            hasValidLocations = true;
        }

        // Add clock-out marker
        if (record.clock_out_latitude && record.clock_out_longitude) {
            const clockOutLat = parseFloat(record.clock_out_latitude);
            const clockOutLng = parseFloat(record.clock_out_longitude);

            // Validate coordinates
            if (isNaN(clockOutLat) || isNaN(clockOutLng)) {
                console.warn('Invalid coordinates for clock-out:', record.clock_out_latitude, record.clock_out_longitude);
                return;
            }

            const clockOutMarker = L.marker([clockOutLat, clockOutLng], {
                icon: L.divIcon({
                    html: `<div class="marker-icon clock-out" style="background-color: #3498db;"></div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                    className: 'custom-marker'
                })
            }).addTo(employeeMap);

            const popupContent = `
                <div style="min-width: 250px;">
                    <h6 style="margin: 0 0 10px; color: #2c3e50;">
                        <i class="fas fa-sign-out-alt me-2" style="color: #3498db;"></i>
                        Clock-Out Location
                    </h6>
                    <p style="margin: 5px 0;"><strong>Date:</strong> ${date}</p>
                    <p style="margin: 5px 0;"><strong>Time:</strong> ${clockOutTime || 'N/A'}</p>
                    <p style="margin: 5px 0;"><strong>Address:</strong> ${record.clock_out_address || 'Location recorded'}</p>
                    <p style="margin: 5px 0;">
                        <strong>Coordinates:</strong>
                        ${clockOutLat.toFixed(6)}, ${clockOutLng.toFixed(6)}
                    </p>
                    <div style="text-align: center; margin-top: 10px;">
                        <button class="btn btn-sm btn-outline-primary view-day-btn"
                                onclick="viewDayDetails('${date}')"
                                style="font-size: 11px; padding: 2px 8px;">
                            <i class="fas fa-eye me-1"></i> View Day Details
                        </button>
                    </div>
                </div>
            `;

            clockOutMarker.bindPopup(popupContent);
            mapMarkers.push(clockOutMarker);
            locations.push([clockOutLat, clockOutLng]);
            hasValidLocations = true;
        }

        // Add polyline if both locations exist and are different
        if (record.clock_in_latitude && record.clock_out_latitude && record.has_location_change) {
            const point1 = [parseFloat(record.clock_in_latitude), parseFloat(record.clock_in_longitude)];
            const point2 = [parseFloat(record.clock_out_latitude), parseFloat(record.clock_out_longitude)];

            // Validate both points
            if (!isNaN(point1[0]) && !isNaN(point1[1]) && !isNaN(point2[0]) && !isNaN(point2[1])) {
                const polyline = L.polyline([point1, point2], {
                    color: '#9b59b6',
                    weight: 3,
                    opacity: 0.7,
                    dashArray: '5, 10'
                }).addTo(employeeMap);

                mapPolylines.push(polyline);
            }
        }
    });

    // Fit map to show all markers
    if (hasValidLocations && locations.length > 0) {
        setTimeout(() => {
            if (employeeMap) {
                const bounds = L.latLngBounds(locations);
                employeeMap.fitBounds(bounds.pad(0.2));
                employeeMap.invalidateSize();
                console.log('Map fitted to bounds with', locations.length, 'locations');
            }
        }, 300);
    } else {
        console.log('No valid locations to display');
    }
}

// Display detailed table
function displayDetails(attendanceData) {
    const detailsBody = document.getElementById('attendanceDetails');

    if (!attendanceData || attendanceData.length === 0) {
        detailsBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <i class="fas fa-user-times fa-2x mb-3"></i>
                    <p>No attendance records found</p>
                </td>
            </tr>
        `;
        return;
    }

    let detailsHTML = '';

    // Sort by date descending
    attendanceData.sort((a, b) => new Date(b.date) - new Date(a.date));

    attendanceData.forEach(record => {
        const date = record.date;
        const clockInTime = record.clock_in_time || 'N/A';
        const clockOutTime = record.clock_out_time || 'N/A';
        const clockInLocation = record.clock_in_address || 'No location data';
        const clockOutLocation = record.clock_out_address || 'No location data';
        const status = record.late ? 'Late' : 'On Time';
        const statusClass = record.late ? 'warning' : 'success';
        const totalHours = record.total_hours || 'N/A';

        // Check if locations are different
        const hasLocationChange = record.has_location_change;
        const locationChangeBadge = hasLocationChange ?
            '<span class="badge bg-info ms-1"><i class="fas fa-exchange-alt me-1"></i> Moved</span>' :
            '<span class="badge bg-secondary ms-1"><i class="fas fa-map-marker me-1"></i> Same</span>';

        detailsHTML += `
            <tr>
                <td class="px-4">
                    <strong>${formatDate(date)}</strong>
                </td>
                <td class="text-center">
                    <div class="fw-bold">${clockInTime}</div>
                    ${record.clock_in_latitude ?
                        '<small class="text-success"><i class="fas fa-check-circle me-1"></i> Location tracked</small>' :
                        '<small class="text-muted"><i class="fas fa-times-circle me-1"></i> No location</small>'
                    }
                </td>
                <td class="text-center">
                    <div class="small">${clockInLocation}</div>
                    ${record.clock_in_latitude ?
                        `<small class="text-muted">${parseFloat(record.clock_in_latitude).toFixed(6)}, ${parseFloat(record.clock_in_longitude).toFixed(6)}</small>` :
                        ''
                    }
                </td>
                <td class="text-center">
                    <div class="fw-bold">${clockOutTime}</div>
                    ${record.clock_out_latitude ?
                        '<small class="text-success"><i class="fas fa-check-circle me-1"></i> Location tracked</small>' :
                        '<small class="text-muted"><i class="fas fa-times-circle me-1"></i> No location</small>'
                    }
                </td>
                <td class="text-center">
                    <div class="small">${clockOutLocation}</div>
                    ${record.clock_out_latitude ?
                        `<small class="text-muted">${parseFloat(record.clock_out_latitude).toFixed(6)}, ${parseFloat(record.clock_out_longitude).toFixed(6)}</small>` :
                        ''
                    }
                    ${locationChangeBadge}
                </td>
                <td class="text-center">
                    <span class="badge bg-${statusClass}">${status}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-light text-dark">${totalHours}</span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-info" onclick="viewDayTimeline('${date}')">
                        <i class="fas fa-eye me-1"></i> View Details
                    </button>
                </td>
            </tr>
        `;
    });

    detailsBody.innerHTML = detailsHTML;
}

// Clear map
function clearMap() {
    if (!employeeMap) return;

    mapMarkers.forEach(marker => {
        if (marker && marker.remove) {
            employeeMap.removeLayer(marker);
        }
    });
    mapMarkers = [];

    mapPolylines.forEach(polyline => {
        if (polyline && polyline.remove) {
            employeeMap.removeLayer(polyline);
        }
    });
    mapPolylines = [];
}

// Clear details
function clearDetails() {
    document.getElementById('attendanceDetails').innerHTML = '';
}

// Show all locations on map
function showAllOnMap() {
    if (currentEmployeeData && currentEmployeeData.attendance) {
        displayMap(currentEmployeeData.attendance);

        // Switch to map tab
        const mapTab = document.querySelector('#map-tab');
        const mapTabPane = document.querySelector('#map-tab-pane');

        // Remove active classes from all tabs
        document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));

        // Add active classes to map tab
        mapTab.classList.add('active');
        mapTabPane.classList.add('show', 'active');

        // Invalidate map size after tab switch
        setTimeout(() => {
            if (employeeMap) {
                employeeMap.invalidateSize();
            }
        }, 300);
    }
}

// View day timeline
function viewDayTimeline(date) {
    if (!currentEmployeeId || !date) return;

    // Show loading in timeline modal
    document.getElementById('timelineDetails').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading timeline details...</p>
        </div>
    `;

    // Open modal
    const timelineModal = new bootstrap.Modal(document.getElementById('timelineModal'));
    timelineModal.show();

    // Fetch timeline data via AJAX
    fetch('{{ route("attendance.getEmployeeTimeline") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_id: currentEmployeeId,
            date: date
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('timelineDetails').innerHTML = data.html;
        } else {
            document.getElementById('timelineDetails').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${data.message || 'Error loading timeline'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('timelineDetails').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading timeline: ${error.message}
            </div>
        `;
    });
}

// View day details on map
function viewDayDetails(date) {
    if (!currentEmployeeData || !currentEmployeeData.attendance) return;

    const dayRecords = currentEmployeeData.attendance.filter(record => record.date === date);

    if (dayRecords.length === 0) return;

    // Create a detailed view for the specific day
    const record = dayRecords[0];

    // Focus on this day's locations on map
    clearMap();

    // Initialize map if needed
    if (!employeeMap) {
        initMap();
    }

    const locations = [];

    // Add clock-in marker
    if (record.clock_in_latitude && record.clock_in_longitude) {
        const clockInLat = parseFloat(record.clock_in_latitude);
        const clockInLng = parseFloat(record.clock_in_longitude);

        if (!isNaN(clockInLat) && !isNaN(clockInLng)) {
            const clockInMarker = L.marker([clockInLat, clockInLng], {
                icon: L.divIcon({
                    html: '<div class="marker-icon clock-in" style="background-color: #2ecc71;"></div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                    className: 'custom-marker'
                })
            }).addTo(employeeMap).bindPopup(`
                <div>
                    <h6><i class="fas fa-sign-in-alt me-2"></i>Clock-In</h6>
                    <p><strong>Time:</strong> ${record.clock_in_time || 'N/A'}</p>
                    <p><strong>Location:</strong> ${record.clock_in_address || 'Recorded'}</p>
                    <p><strong>Coordinates:</strong> ${clockInLat.toFixed(6)}, ${clockInLng.toFixed(6)}</p>
                </div>
            `);

            locations.push([clockInLat, clockInLng]);
        }
    }

    // Add clock-out marker
    if (record.clock_out_latitude && record.clock_out_longitude) {
        const clockOutLat = parseFloat(record.clock_out_latitude);
        const clockOutLng = parseFloat(record.clock_out_longitude);

        if (!isNaN(clockOutLat) && !isNaN(clockOutLng)) {
            const clockOutMarker = L.marker([clockOutLat, clockOutLng], {
                icon: L.divIcon({
                    html: '<div class="marker-icon clock-out" style="background-color: #3498db;"></div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                    className: 'custom-marker'
                })
            }).addTo(employeeMap).bindPopup(`
                <div>
                    <h6><i class="fas fa-sign-out-alt me-2"></i>Clock-Out</h6>
                    <p><strong>Time:</strong> ${record.clock_out_time || 'N/A'}</p>
                    <p><strong>Location:</strong> ${record.clock_out_address || 'Recorded'}</p>
                    <p><strong>Coordinates:</strong> ${clockOutLat.toFixed(6)}, ${clockOutLng.toFixed(6)}</p>
                </div>
            `);

            locations.push([clockOutLat, clockOutLng]);
        }
    }

    // Add polyline if both locations exist
    if (record.clock_in_latitude && record.clock_out_latitude && record.has_location_change) {
        const point1 = [parseFloat(record.clock_in_latitude), parseFloat(record.clock_in_longitude)];
        const point2 = [parseFloat(record.clock_out_latitude), parseFloat(record.clock_out_longitude)];

        if (!isNaN(point1[0]) && !isNaN(point1[1]) && !isNaN(point2[0]) && !isNaN(point2[1])) {
            L.polyline([point1, point2], {
                color: '#9b59b6',
                weight: 3,
                opacity: 0.7,
                dashArray: '5, 10'
            }).addTo(employeeMap);
        }
    }

    // Fit map to show markers
    if (locations.length > 0) {
        setTimeout(() => {
            if (employeeMap) {
                const bounds = L.latLngBounds(locations);
                employeeMap.fitBounds(bounds.pad(0.2));
                employeeMap.invalidateSize();
            }
        }, 300);
    }

    // Switch to map tab
    const mapTab = document.querySelector('#map-tab');
    const mapTabPane = document.querySelector('#map-tab-pane');

    document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));

    mapTab.classList.add('active');
    mapTabPane.classList.add('show', 'active');

    // Invalidate map size
    setTimeout(() => {
        if (employeeMap) {
            employeeMap.invalidateSize();
        }
    }, 300);

    // Show timeline modal
    viewDayTimeline(date);
}

// Show loading state
function showLoading(show) {
    const searchBtn = document.getElementById('searchEmployeeBtn');
    if (show) {
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Searching...';
        searchBtn.disabled = true;
    } else {
        searchBtn.innerHTML = '<i class="fas fa-search me-1"></i> Search';
        searchBtn.disabled = false;
    }
}

// Format date
function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        return dateString;
    }
}

// Format time
function formatTime(timeString) {
    try {
        if (!timeString || timeString === 'N/A') return 'N/A';
        const timeParts = timeString.split(':');
        if (timeParts.length >= 2) {
            let hours = parseInt(timeParts[0]);
            const minutes = timeParts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            return hours + ':' + minutes + ' ' + ampm;
        }
        return timeString;
    } catch (e) {
        return timeString;
    }
}

// Handle tab changes to resize map
document.addEventListener('DOMContentLoaded', function() {
    const tabEl = document.querySelector('#myTab');
    if (tabEl) {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            if (event.target.id === 'map-tab') {
                setTimeout(() => {
                    if (employeeMap) {
                        employeeMap.invalidateSize();
                        console.log('Map resized after tab switch');
                    }
                }, 300);
            }
        });
    }
});
</script>

<style>
    .marker-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        color: white;
    }

    .marker-icon.clock-in {
        background-color: #2ecc71 !important;
    }

    .marker-icon.clock-out {
        background-color: #3498db !important;
    }

    .custom-marker {
        background: transparent;
        border: none;
    }

    .view-day-btn {
        cursor: pointer;
    }

    #locationMap {
        min-height: 500px;
        width: 100%;
    }

    /* Ensure leaflet map container is visible */
    .leaflet-container {
        width: 100% !important;
        height: 100% !important;
        border-radius: 8px;
    }
</style>
@endpush
