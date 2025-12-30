<form method="POST" action="{{ $attendance ? route('attendance.update', $attendance->id) : route('attendance.store') }}">
    @csrf
    @if($attendance)
        @method('PUT')
    @endif

    <!-- Hidden User & Date -->
    <input type="hidden" name="user_id[]" value="{{ $userId }}">
    <input type="hidden" name="date" value="{{ $date }}">

    <!-- Employee Info -->
    <div class="mb-3 d-flex align-items-center">
    <img src="{{ $employee->profile_image ? asset($employee->profile_image) : asset('images/default-avatar.png') }}" 
         alt="{{ $employee->name }}" class="rounded-circle me-3" style="width:60px; height:60px; object-fit:cover;">
    <h5 class="mb-0">{{ $employee->name }} ({{ $employee->designation ?? 'N/A' }})</h5>
</div>

    <div class="row mb-3">
       

        {{-- Location --}}
        <div class="col-md-4">
            <label class="form-label">Location <sup class="text-danger">*</sup></label>
            <select name="location_id" class="form-select" required>
                @foreach ($location as $loc)
                    <option value="{{ $loc->id }}" 
                        {{ ($attendance->location_id ?? old('location_id')) == $loc->id ? 'selected' : ($loc->is_default ? 'selected' : '') }}>
                        {{ $loc->location }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row mb-3">
        {{-- Clock In --}}
        <div class="col-md-3">
            <label class="form-label">Clock In</label>
            <input type="time" name="clock_in" class="form-control" required
                value="{{ $attendance ? (\Carbon\Carbon::parse($attendance->clock_in)->format('H:i') ?? '') : '10:30' }}">
        </div>

        {{-- Clock Out --}}
        <div class="col-md-3">
            <label class="form-label">Clock Out</label>
            <input type="time" name="clock_out" class="form-control" 
                value="{{ $attendance ? (\Carbon\Carbon::parse($attendance->clock_out)->format('H:i') ?? '') : '19:30' }}">
        </div>

        {{-- Status --}}
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                @php $status = $attendance->status ?? 'absent'; @endphp
                <option value="present" {{ $status == 'present' ? 'selected' : '' }}>Present</option>
                <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Absent</option>
                <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Late</option>
                <option value="half_day" {{ $status == 'half_day' ? 'selected' : '' }}>Half Day</option>
                <option value="leave" {{ $status == 'leave' ? 'selected' : '' }}>Leave</option>
                <option value="holiday" {{ $status == 'holiday' ? 'selected' : '' }}>Holiday</option>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        {{-- Late / Half Day Radios --}}
        <div class="col-md-3">
            <label>Late</label>
            <div class="d-flex">
                <div class="form-check me-3">
                    <input class="form-check-input" type="radio" name="late" value="yes" {{ ($attendance->status ?? '') == 'late' ? 'checked' : '' }}>
                    <label class="form-check-label">Yes</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="late" value="no" {{ ($attendance->status ?? '') != 'late' ? 'checked' : '' }}>
                    <label class="form-check-label">No</label>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <label>Half Day</label>
            <div class="d-flex">
                <div class="form-check me-3">
                    <input class="form-check-input" type="radio" name="half_day" value="yes" {{ ($attendance->status ?? '') == 'half_day' ? 'checked' : '' }}>
                    <label class="form-check-label">Yes</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="half_day" value="no" {{ ($attendance->status ?? '') != 'half_day' ? 'checked' : '' }}>
                    <label class="form-check-label">No</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        {{-- Working From --}}
        <div class="col-md-3">
            <label class="form-label">Working From</label>
            <select name="work_from_type" id="work_from_type" class="form-select">
                <option value="office" {{ ($attendance->work_from_type ?? '') == 'office' ? 'selected' : '' }}>Office</option>
                <option value="home" {{ ($attendance->work_from_type ?? '') == 'home' ? 'selected' : '' }}>Home</option>
                <option value="other" {{ ($attendance->work_from_type ?? '') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        {{-- Other Location --}}
        <div class="col-md-3 {{ ($attendance->work_from_type ?? '') == 'other' ? '' : 'd-none' }}" id="other_location_div">
            <label class="form-label">Other Location</label>
            <input type="text" name="working_from" class="form-control" value="{{ $attendance->working_from ?? '' }}">
        </div>

        {{-- Overwrite Attendance --}}
        <div class="col-md-3 mt-4">
            <div class="form-check">
                <input type="checkbox" name="overwrite_attendance" class="form-check-input" value="yes" {{ ($attendance->overwrite_attendance ?? '') == 'yes' ? 'checked' : '' }}>
                <label class="form-check-label">Attendance Overwrite</label>
            </div>
        </div>
    </div>

    <button class="btn btn-success">{{ $attendance ? 'Update' : 'Save' }} Attendance</button>
    <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
</form>
