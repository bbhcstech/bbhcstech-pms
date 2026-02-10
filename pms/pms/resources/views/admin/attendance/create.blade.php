@extends('admin.layout.app')

@section('title', 'Create Attendance')

@section('content')

<main class="main">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold text-dark">Add Attendance</h1>
                <p class="text-muted mb-0">Mark attendance for employees</p>
            </div>
        </div>

        <!-- Success/Error Alert -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <div class="flex-grow-1">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Main Form Card -->
        <div class="card border mb-4">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf

                    <!-- Employee Selection Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="department_id" class="form-label fw-semibold text-dark">Department</label>
                            <select name="department_id" class="form-select" required>
                                <option value="0">-- Select Department --</option>
                                @foreach ($departments as $team)
                                    <option value="{{ $team->id }}">{{ $team->dpt_name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="selectEmployee" class="form-label fw-semibold text-dark">Employees</label>
                            <select class="form-control multiple-users" multiple name="user_id[]"
                                    id="selectEmployee" data-live-search="true" data-size="8">
                                <option value="">-- Select Employees --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->designation ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Location and Date Selection -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="location_id" class="form-label fw-semibold text-dark">Location</label>
                            <select name="location_id" id="location_id" class="form-select" required>
                                @foreach ($location as $locations)
                                    <option @if ($locations->is_default == 1) selected @endif value="{{ $locations->id }}">
                                        {{ $locations->location }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Mark Attendance By -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Mark Attendance By</label>
                            <div class="d-flex align-items-center h-100">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="mark_attendance_by" id="mark_attendance_by_month" value="month" checked>
                                    <label class="form-check-label" for="mark_attendance_by_month">
                                        Month
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mark_attendance_by" id="mark_attendance_by_dates" value="date">
                                    <label class="form-check-label" for="mark_attendance_by_dates">
                                        Date Range
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Year & Month Section -->
                        <div class="col-md-3" id="year_section">
                            <label for="year" class="form-label fw-semibold text-dark">Year</label>
                            <select name="year" id="year" class="form-select">
                                <option value="">-- Select Year --</option>
                                @for ($i = $year; $i >= $year - 4; $i--)
                                    <option @if ($i == $year) selected @endif value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3" id="month_section">
                            <label for="month" class="form-label fw-semibold text-dark">Month</label>
                            <select id="month" name="month" class="form-select">
                                <option value="">-- Select Month --</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>

                        <!-- Date Range Section -->
                        <div class="col-md-6" id="date_section" style="display: none;">
                            <label for="date_range" class="form-label fw-semibold text-dark">Date Range</label>
                            <input type="text" class="form-control" id="date_range" name="date_range" placeholder="MM/DD/YYYY - MM/DD/YYYY">
                            <small class="text-muted">Select start and end dates</small>
                        </div>
                    </div>

                    <!-- Time and Status Section -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="clock_in" class="form-label fw-semibold text-dark">Clock In</label>
                            <input type="time" name="clock_in" class="form-control"
                                   value="10:30" required max="23:59">
                            @error('clock_in')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="clock_out" class="form-label fw-semibold text-dark">Clock Out</label>
                            <input type="time" name="clock_out" class="form-control"
                                   value="19:30" max="23:59">
                            @error('clock_out')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Hidden Status Field -->
                        <input type="hidden" name="status" id="status" value="absent">

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark mb-2">Late</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" id="late_yes" name="late" value="yes">
                                    <label class="form-check-label" for="late_yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="late_no" name="late" value="no" checked>
                                    <label class="form-check-label" for="late_no">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark mb-2">Half Day</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" id="half_day_yes" name="half_day" value="yes">
                                    <label class="form-check-label" for="half_day_yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="half_day_no" name="half_day" value="no" checked>
                                    <label class="form-check-label" for="half_day_no">No</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Half Day Duration -->
                    <div class="row mb-4" id="half_day_duration_div" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Half Day Duration</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" id="first_half_day_yes" name="half_day_duration" value="first_half" checked>
                                    <label class="form-check-label" for="first_half_day_yes">First Half</label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" id="first_half_day_no" name="half_day_duration" value="second_half">
                                    <label class="form-check-label" for="first_half_day_no">Second Half</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Work Location and Options -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="work_from_type" class="form-label fw-semibold text-dark">Working From</label>
                            <select name="work_from_type" id="work_from_type" class="form-select" required>
                                <option value="office">Office</option>
                                <option value="home">Home</option>
                                <option value="other">Other</option>
                            </select>
                            @error('work_from_type')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Other Location Input -->
                        <div class="col-md-4 mt-2 d-none" id="other_location_div">
                            <label for="other_location" class="form-label fw-semibold text-dark">Other Location</label>
                            <input type="text" name="working_from" id="other_location" class="form-control">
                            @error('other_location')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="overwrite_attendance" id="overwrite_attendance" class="form-check-input" value="yes">
                                <label class="form-check-label fw-semibold text-dark" for="overwrite_attendance">
                                    Overwrite Existing Attendance
                                </label>
                            </div>
                        </div>
                    </div> -->

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-2"></i>Save Attendance
                        </button>
                        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<style>
    .card {
        border-color: #e0e0e0;
    }

    .form-label {
        color: #495057;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-color: #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .form-check-label {
        font-size: 0.875rem;
        color: #495057;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        font-weight: 500;
    }

    .btn-outline-secondary {
        font-weight: 500;
    }

    .alert {
        border-radius: 0.375rem;
        border: 1px solid transparent;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .text-muted {
        color: #6c757d !important;
        font-size: 0.8125rem;
    }

    .fw-semibold {
        font-weight: 600 !important;
    }

    .bootstrap-select .dropdown-toggle {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .bootstrap-select .dropdown-toggle:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .bootstrap-select .dropdown-menu {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .daterangepicker {
        z-index: 1060 !important;
        font-family: inherit;
    }
</style>
@endpush

@push('scripts')
<!-- Moment.js -->
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<!-- Daterangepicker -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- Bootstrap Select -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Date range toggle
        const monthRadio = document.getElementById("mark_attendance_by_month");
        const dateRadio = document.getElementById("mark_attendance_by_dates");
        const yearSection = document.getElementById("year_section");
        const monthSection = document.getElementById("month_section");
        const dateSection = document.getElementById("date_section");

        function toggleSections() {
            if (monthRadio.checked) {
                yearSection.style.display = "block";
                monthSection.style.display = "block";
                dateSection.style.display = "none";
            } else {
                yearSection.style.display = "none";
                monthSection.style.display = "none";
                dateSection.style.display = "block";
            }
        }

        toggleSections();
        monthRadio.addEventListener("change", toggleSections);
        dateRadio.addEventListener("change", toggleSections);

        // Initialize date range picker
        const dateRangeInput = document.getElementById("date_range");
        if (dateRangeInput) {
            $(dateRangeInput).daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MM/DD/YYYY'
                }
            });

            $(dateRangeInput).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });

            $(dateRangeInput).on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        }

        // Half day duration toggle
        const halfDayYes = document.getElementById("half_day_yes");
        const halfDayNo = document.getElementById("half_day_no");
        const durationDiv = document.getElementById("half_day_duration_div");

        function toggleDuration() {
            durationDiv.style.display = halfDayYes.checked ? "block" : "none";
        }

        toggleDuration();
        halfDayYes.addEventListener("change", toggleDuration);
        halfDayNo.addEventListener("change", toggleDuration);

        // Other location toggle
        const workFromType = document.getElementById("work_from_type");
        const otherDiv = document.getElementById("other_location_div");
        const otherInput = document.getElementById("other_location");

        workFromType.addEventListener("change", function() {
            if (this.value === "other") {
                otherDiv.classList.remove("d-none");
                otherInput.setAttribute("required", "required");
            } else {
                otherDiv.classList.add("d-none");
                otherInput.removeAttribute("required");
                otherInput.value = "";
            }
        });

        // Status update
        const statusInput = document.getElementById("status");

        function updateStatus() {
            let status = "present";

            @if(empty($attendanceData))
                status = "absent";
            @endif

            if (document.getElementById("late_yes").checked) {
                status = "late";
            }

            if (document.getElementById("half_day_yes").checked) {
                status = "half_day";
            }

            statusInput.value = status;
        }

        document.querySelectorAll("input[name='late'], input[name='half_day']").forEach(input => {
            input.addEventListener("change", updateStatus);
        });

        updateStatus();

        // Initialize selectpicker
        $("#selectEmployee").selectpicker({
            actionsBox: true,
            selectAllText: "Select All",
            deselectAllText: "Deselect All",
            multipleSeparator: ", ",
            selectedTextFormat: "count > 3",
            countSelectedText: function(selected, total) {
                return selected + " selected";
            }
        });
    });
</script>
@endpush
