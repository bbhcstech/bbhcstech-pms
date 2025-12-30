<div class="attendance-details">
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Employee Information</h5>
                    <p><strong>Name:</strong> {{ $attendance->user->name ?? '-' }}</p>
                    <p><strong>Designation:</strong> {{ $attendance->user->employeeDetail->designation->name ?? '-' }}</p>
                    <p><strong>Date:</strong> {{ optional($attendanceDate)->format('d M Y (l)') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Shift Information</h5>
                    <p><strong>Location:</strong> {{ $attendance->location ?? 'Office' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
           <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Attendance Summary</h5>

                @if(auth()->user()->role === 'admin')
                <div class="dropdown">
                    <button class="btn btn-sm  border-0" type="button" id="summaryActions" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded fs-4"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="summaryActions">
                        <li>
                            <a class="dropdown-item text-primary edit-attendance-summary"
                               href="javascript:void(0);"
                               data-attendance-id="{{ $attendance->id }}"
                               data-user-id="{{ $attendance->user_id }}"
                               data-date="{{ optional($attendanceDate)->format('Y-m-d') }}">
                                <i class="bx bx-edit-alt me-2"></i> Edit
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger delete-attendance-summary"
                               href="javascript:void(0);"
                               data-attendance-id="{{ $attendance->id }}">
                                <i class="bx bx-trash me-2"></i> Delete
                            </a>
                        </li>
                    </ul>
                </div>
                @endif
            </div>

            <div class="row">
                <div class="col-md-3">
                    <p><strong>Clock In:</strong>
                        @if(!empty($startTime))
                            {{ $startTime->timezone($companyTimezone ?? config('app.timezone'))->format('h:i A') }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="col-md-3">
                    <p><strong>Clock Out:</strong>
                        @if($notClockedOut)
                            Did not clock out
                        @elseif(!empty($endTime))
                            {{ $endTime->timezone($companyTimezone ?? config('app.timezone'))->format('h:i A') }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="col-md-3">
                   <p><strong>Total Duration:</strong> {{ $totalTimeFormatted ?? '00:00:00' }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Status:</strong>
                        @if($attendance->late == 'yes')
                            <span class="badge bg-warning">Late</span>
                        @elseif($attendance->half_day == 'yes')
                            <span class="badge bg-info">Half Day</span>
                        @else
                            <span class="badge bg-success">Present</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Activity Log</h5>

            <div class="activity-log">
                @forelse($attendanceActivity as $activity)
                    @php
                        // controller supplies Carbons or nulls
                        $inDt = $activity->in_dt ?? null;
                        $outDt = $activity->out_dt ?? null;
                        $outForDuration = $activity->out_for_duration ?? null;
                        $durationSeconds = $activity->duration_seconds;
                        $location = $activity->location ?? ($activity->raw->location ?? 'Office');
                    @endphp

                    <div class="activity-item {{ $outDt ? 'clock-out' : 'clock-in' }} mb-3 p-2 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="d-block mb-1">{{ $outDt ? 'Clock Out' : 'Clock In' }}</strong>
                                <small class="text-muted">
                                    @if($inDt)
                                        {{ $inDt->timezone($companyTimezone ?? config('app.timezone'))->format('h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </small>
                            </div>

                            <div class="text-end">
                                <small class="text-muted d-block">{{ $location }}</small>
                                @if($outDt)
                                    <small class="text-muted d-block">
                                        @if($outDt)
                                            {{ $outDt->timezone($companyTimezone ?? config('app.timezone'))->format('h:i A') }}
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>

                        @if($durationSeconds !== null && $inDt && $outForDuration)
                            <div class="mt-2">
                                <small>
                                    <strong>Duration:</strong>
                                    {{ $activity->duration_human }} ({{ gmdate('H:i:s', max(0, $durationSeconds)) }})
                                </small>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted py-3">
                        No activity found for this date.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
    new bootstrap.Dropdown(el);
});
</script>

<script>
    $(document).on('click', '.edit-attendance-summary', function (e) {
        e.preventDefault();

        var attendanceId = $(this).data('attendance-id');
        var userId = $(this).data('user-id');
        var date = $(this).data('date');

        var url = "{{ url('attendance/edit') }}?attendance_id=" + attendanceId + "&user_id=" + userId + "&date=" + date;

        $('#editAttendanceModal').remove();
        var editModalHtml = `
            <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" style="z-index: 2000;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-secondary text-white">
                            <h5 class="modal-title">Edit Attendance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="editAttendanceBody">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(editModalHtml);
        var editModal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
        editModal.show();

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                $('#editAttendanceBody').html(response);
            },
            error: function () {
                $('#editAttendanceBody').html('<div class="alert alert-danger">Error loading attendance form.</div>');
            }
        });
    });

    $(document).on('click', '.delete-attendance-summary', function (e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this attendance record?')) return;

        var attendanceId = $(this).data('attendance-id');

        $.ajax({
            url: "{{ url('attendance') }}/" + attendanceId,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {
                alert('Attendance record deleted successfully.');
                location.reload();
            },
            error: function () {
                alert('Error deleting attendance record.');
            }
        });
    });
</script>

<style>
    .modal-backdrop.show:nth-of-type(2) {
        z-index: 1155
    }
    #editAttendanceModal {
        z-index: 1160  !important;
    }
    #attendanceDetailsModal {
        z-index: 1155 !important;
    }
</style>
