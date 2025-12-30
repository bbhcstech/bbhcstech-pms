
@extends('admin.layout.app')

@section('title', 'Create Attendance')  

@section('content')

  <main class="main">
    <div class="content-wrapper py-4 px-3" style="background-color: #f5f7fa; min-height: 100vh;">
        <div class="container-fluid">
            <h4 class="fw-bold mb-3">Add Attendance</h4>
<br>
           @if(session('error'))
    <div class="alert alert-danger" style="background-color: #dc3545; color: white; border-color: #dc3545;">
        {{ session('error') }}
    </div>
@endif
<br>

            <form method="POST" action="{{ route('attendance.store') }}">
                @csrf
                <div class="row mb-3">
                    
                    <div class="col-md-4">
                        <label for="user_id" class="form-label">Department <sup class="text-danger">*</sup></label>
                        <select name="department_id" class="form-select" required>
                           <option value="0">--</option>
                            @foreach ($departments as $team)
                                <option value="{{ $team->id }}">{{ $team->dpt_name }}</option>
                            @endforeach
                            </select>
                            @error('department_id')
                            <span class="text-danger">{{ $message }}</span>
                           @enderror
                    </div>
                            
                            
                    <div class="col-md-4">
                        <label for="user_id" class="form-label">Employee <sup class="text-danger">*</sup></label>
                        
                           <select class="form-control multiple-users" multiple name="user_id[]"
                                    id="selectEmployee" data-live-search="true" data-size="8">
                            <option value="">-- Select Employee --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->designation ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!--<div class="col-md-4">-->
                    <!--    <label for="date" class="form-label">Date <sup class="text-danger">*</sup></label>-->
                    <!--    <input type="date" name="date" class="form-control" required max="{{ now()->toDateString() }}">-->
                    <!--    @error('date')-->
                    <!--        <span class="text-danger">{{ $message }}</span>-->
                    <!--    @enderror-->
                    <!--</div>-->
                </div>
                 <div class="row mb-3">
                    
                        {{-- Location --}}
                        <div class="col-md-3">
                            <label for="location_id" class="form-label">Location <sup class="text-danger">*</sup></label>
                            <select name="location_id" id="location_id" class="form-select" required>
                                @foreach ($location as $locations)
                                    <option @if ($locations->is_default == 1) selected @endif value="{{ $locations->id }}">
                                        {{ $locations->location }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    
                       {{-- Mark Attendance By --}}
                        <div class="col-md-3">
                            <label class="form-label">Mark Attendance By <sup class="text-danger">*</sup></label>
                            <div class="d-flex align-items-center">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="mark_attendance_by" id="mark_attendance_by_month" value="month" checked>
                                    <label class="form-check-label" for="mark_attendance_by_month">
                                        Month
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mark_attendance_by" id="mark_attendance_by_dates" value="date">
                                    <label class="form-check-label" for="mark_attendance_by_dates">
                                        Date
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Year (shown only if Month is selected) --}}
                        <div class="col-md-3" id="year_section">
                            <label for="year" class="form-label">Year <sup class="text-danger">*</sup></label>
                            <select name="year" id="year" class="form-select">
                                <option value="">--</option>
                                @for ($i = $year; $i >= $year - 4; $i--)
                                    <option @if ($i == $year) selected @endif value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        
                        {{-- Month (shown only if Month is selected) --}}
                        <div class="col-md-3" id="month_section">
                            <label for="month" class="form-label">Month <sup class="text-danger">*</sup></label>
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
                        
                    
                        {{-- Dates (shown only if Date is selected) --}}
                        <div class="col-md-6" id="date_section" style="display: none;">
                            <label for="date_range" class="form-label">Date Range <sup class="text-danger">*</sup></label>
                            <input type="text" class="form-control" id="date_range" name="date_range" placeholder="MM/DD/YYYY - MM/DD/YYYY">
                            <small class="text-muted">Select a start and end date.</small>
                        </div>
                    
                    </div>


                <div class="row mb-3">
                    
                    
                   <div class="col-md-3">
                        <label for="clock_in" class="form-label">Clock In <sup class="text-danger">*</sup></label>
                        <input type="time" name="clock_in" class="form-control" 
                               value="10:30" required max="23:59">
                        @error('clock_in')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="clock_out" class="form-label">Clock Out <sup class="text-danger">*</sup></label>
                        <input type="time" name="clock_out" class="form-control" 
                               value="19:30" max="23:59">
                        @error('clock_out')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    
                    
                    <!--<div class="col-md-4">-->
                    <!--    <label for="status" class="form-label">Status <sup class="text-danger">*</sup></label>-->
                    <!--    <select name="status" class="form-select" required>-->
                    <!--        <option value="present">✔️ Present</option>-->
                    <!--        <option value="absent">❌ Absent</option>-->
                    <!--        <option value="late">⚠️ Late</option>-->
                    <!--        <option value="half_day">⏳ Half Day</option>-->
                    <!--        <option value="leave">🛫 Leave</option>-->
                    <!--        <option value="holiday">⭐ Holiday</option>-->
                    <!--    </select>-->
                    <!--</div>-->
                    
                    <!-- Hidden Status Field -->
                 <input type="hidden" name="status" id="status" value="absent"> <!-- default -->
                 
                 
                     <div class="col-md-3">
                    <label for="late_yes">Late</label>
                    <div class="d-flex">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" id="late_yes" name="late" value="yes">
                            <label class="form-check-label" for="late_yes">yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="late_no" name="late" value="no" checked>
                            <label class="form-check-label" for="late_no">no</label>
                        </div>
                    </div>
                </div>

                    
                  <div class="col-md-3">
                    <label for="half_day_yes">Half Day</label>
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
                
                <!-- Select Duration (Initially Hidden) -->
                <div class="col-md-3" id="half_day_duration_div" style="display: none;">
                    <label for="duration">Select Duration</label>
                    <div class="d-flex">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" id="first_half_day_yes" name="half_day_duration" value="first_half" checked>
                            <label class="form-check-label" for="first_half_day_yes">First Half</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="first_half_day_no" name="half_day_duration" value="second_half">
                            <label class="form-check-label" for="first_half_day_no">Second Half</label>
                        </div>
                    </div>
                </div>
                  

                </div>
                
                  <div class="row mb-3">
                    
                   <div class="col-md-3">
                    <label for="work_from_type" class="form-label">Working From</label>
                    <select name="work_from_type" id="work_from_type" class="form-select" required>
                        <option value="office">Office</option>
                        <option value="home">Home</option>
                        <option value="other">Other</option>
                    </select>
                    @error('work_from_type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Hidden input for "Other" location -->
                <div class="col-md-3 mt-2 d-none" id="other_location_div">
                    <label for="other_location" class="form-label">Other Locations <sup class="text-danger">*</sup></label>
                    <input type="text" name="working_from" id="other_location" class="form-control">
                    @error('other_location')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                    
                   
                    
                    <div class="col-lg-4 col-md-6 mt-5">
                        <div class="form-check">
                            <input type="checkbox" name="overwrite_attendance" id="overwrite_attendance" class="form-check-input" value="yes" >
                            <label class="form-check-label" for="overwrite_attendance">
                               Attendance Overwrite
                            </label>
                          
                        </div>
                    </div>

                    
                    </div>

                <button class="btn btn-success">Save Attendance</button>
                <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</main>
@endsection
@push('scripts')
<!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />



<!-- Moment.js -->
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>

<!-- Daterangepicker -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>


<script>

    document.addEventListener("DOMContentLoaded", function () {
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

        // Run on load + when switching
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
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const halfDayYes = document.getElementById("half_day_yes");
    const halfDayNo = document.getElementById("half_day_no");
    const durationDiv = document.getElementById("half_day_duration_div");

    function toggleDuration() {
        if (halfDayYes.checked) {
            durationDiv.style.display = "block";
        } else {
            durationDiv.style.display = "none";
        }
    }

    // Initial check
    toggleDuration();

    // Event listeners
    halfDayYes.addEventListener("change", toggleDuration);
    halfDayNo.addEventListener("change", toggleDuration);
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let workFromType = document.getElementById("work_from_type");
        let otherDiv = document.getElementById("other_location_div");
        let otherInput = document.getElementById("other_location");

        workFromType.addEventListener("change", function() {
            if (this.value === "other") {
                otherDiv.classList.remove("d-none");
                otherInput.setAttribute("required", "required"); // make mandatory
            } else {
                otherDiv.classList.add("d-none");
                otherInput.removeAttribute("required"); // not mandatory
                otherInput.value = ""; // clear value
            }
        });
    });
</script>

<script>

    $(document).ready(function() {
        $("#selectEmployee").selectpicker({
            actionsBox: true,
            selectAllText: "selectAll",
            deselectAllText: "deselectAll",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + "Selected";
            }
        });

        $('#multi_date').daterangepicker({
            linkedCalendars: false,
            multidate: true,
            todayHighlight: true,
            format: 'yyyy-mm-d'
        });

        $('input[type=radio][name=mark_attendance_by]').change(function() {
            if(this.value=='date') {
                $('#multi_date').daterangepicker('clearDates').daterangepicker({
                    linkedCalendars: false,
                    multidate: true,
                    todayHighlight: true,
                    format: 'yyyy-mm-d',
                    maxDate: new Date(),
                });
            }

        });
        $('#work_from_type').change(function(){
            ($(this).val() == 'other') ? $('#other_place').show() : $('#other_place').hide();
        });

        $('#start_time, #end_time').timepicker({
            showMeridian: (company.time_format == 'H:i' ? false : true)
        });
        
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let statusInput = document.getElementById("status");

        function updateStatus() {
            // Default
            let status = "present";

            // If no attendance data (example: you can check your condition)
            @if(empty($attendanceData))
                status = "absent";
            @endif

            // If late is yes
            if (document.getElementById("late_yes").checked) {
                status = "late";
            }

            // If half day is yes
            if (document.getElementById("half_day_yes").checked) {
                status = "half_day";
            }

            statusInput.value = status;
        }

        // Trigger on change
        document.querySelectorAll("input[name='late'], input[name='half_day']").forEach(input => {
            input.addEventListener("change", updateStatus);
        });

        // Initial set
        updateStatus();
    });
</script>
@endpush




            