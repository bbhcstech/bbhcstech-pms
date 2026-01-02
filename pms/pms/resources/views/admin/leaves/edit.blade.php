@extends('admin.layout.app')

@section('title', 'Edit Leave')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-2">Edit Leave Request</h1>
            <p class="text-muted mb-0">Update leave details for {{ $leave->user?->name ?? 'Employee' }}</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Leaves
            </a>
        </div>
    </div>

    <!-- Error Alert -->
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div>
                <h5 class="alert-heading mb-2">Please fix the following errors:</h5>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Form Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent py-3">
            <h5 class="card-title mb-0">
                <i class="bi bi-pencil-square me-2"></i>Edit Leave Application
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('leaves.update', $leave->id) }}" enctype="multipart/form-data" id="editLeaveForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Employee Selection (Admin Only) -->
                    @if(auth()->user()->role === 'admin')
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="user_id" class="form-label">
                            Employee <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $leave->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->designation ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">Current: {{ $leave->user?->name ?? 'Not assigned' }}</small>
                    </div>
                    @endif

                    <!-- Leave Type -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="type" class="form-label">
                            Leave Type <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                            <select name="type" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                <option value="sick" {{ $leave->type == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="casual" {{ $leave->type == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                                <option value="leave-without-pay" {{ $leave->type == 'leave-without-pay' ? 'selected' : '' }}>Leave Without Pay</option>
                            </select>
                        </div>
                        <small class="text-muted">Current: {{ ucfirst($leave->type) }} Leave</small>
                    </div>

                    <!-- Status -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="status" class="form-label">
                            Status <span class="text-danger">*</span>
                            <i class="bi bi-info-circle text-muted ms-1"
                               data-bs-toggle="tooltip"
                               title="Change the current status of the leave request"></i>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                @php
                                    $statusIcons = [
                                        'pending' => 'bi-clock',
                                        'approved' => 'bi-check-circle',
                                        'rejected' => 'bi-x-circle'
                                    ];
                                @endphp
                                <i class="bi {{ $statusIcons[$leave->status] ?? 'bi-question-circle' }}"></i>
                            </span>
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ $leave->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $leave->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $leave->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'pending' ? 'warning' : 'danger') }} bg-opacity-10 text-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'pending' ? 'warning' : 'danger') }}">
                                Current: {{ ucfirst($leave->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Duration Selection -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="duration" class="form-label">
                            Duration <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-clock"></i></span>
                            <select name="duration" id="duration" class="form-select" required
                                    onchange="toggleDateFields(this.value)">
                                <option value="">-- Select Duration --</option>
                                <option value="full-day" {{ $leave->duration == 'full-day' ? 'selected' : '' }}>Full Day</option>
                                <option value="multiple" {{ $leave->duration == 'multiple' ? 'selected' : '' }}>Multiple Days</option>
                                <option value="first-half" {{ $leave->duration == 'first-half' ? 'selected' : '' }}>First Half</option>
                                <option value="second-half" {{ $leave->duration == 'second-half' ? 'selected' : '' }}>Second Half</option>
                            </select>
                        </div>
                        <small class="text-muted">Current: {{ ucfirst(str_replace('-', ' ', $leave->duration)) }}</small>
                    </div>

                    <!-- Single Date Field -->
                    <div class="col-lg-4 col-md-6 mb-4 {{ $leave->duration == 'multiple' ? 'd-none' : '' }}" id="single-date">
                        <label for="date" class="form-label">
                            Date <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                            <input type="date" name="date" id="date" class="form-control"
                                   value="{{ $leave->duration !== 'multiple' ? $leave->date : '' }}"
                                   required>
                        </div>
                        @if($leave->duration !== 'multiple')
                        <small class="text-muted">Current: {{ \Carbon\Carbon::parse($leave->date)->format('d M, Y') }}</small>
                        @endif
                    </div>

                    <!-- Multi Date Fields -->
                    <div class="col-lg-8 col-md-12 mb-4 {{ $leave->duration == 'multiple' ? '' : 'd-none' }}" id="multi-date">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">
                                    Start Date <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-plus"></i></span>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                           value="{{ $leave->duration == 'multiple' ? $leave->start_date : '' }}"
                                           {{ $leave->duration == 'multiple' ? 'required' : '' }}>
                                </div>
                                @if($leave->duration == 'multiple')
                                <small class="text-muted">Current: {{ \Carbon\Carbon::parse($leave->start_date)->format('d M, Y') }}</small>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">
                                    End Date <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-minus"></i></span>
                                    <input type="date" name="end_date" id="end_date" class="form-control"
                                           value="{{ $leave->duration == 'multiple' ? $leave->end_date : '' }}"
                                           {{ $leave->duration == 'multiple' ? 'required' : '' }}>
                                </div>
                                @if($leave->duration == 'multiple')
                                <small class="text-muted">Current: {{ \Carbon\Carbon::parse($leave->end_date)->format('d M, Y') }}</small>
                                @endif
                            </div>
                        </div>
                        @if($leave->duration == 'multiple')
                        <div class="alert alert-info bg-opacity-10 border-0 mt-2">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Total Days:</strong>
                            {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} days
                        </div>
                        @endif
                    </div>

                    <!-- Reason for Absence -->
                    <div class="col-lg-8 col-md-12 mb-4">
                        <label for="reason" class="form-label">
                            Reason for Absence <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                            <textarea name="reason" id="reason" class="form-control" rows="3"
                                      placeholder="Provide details for your leave request..."
                                      required>{{ $leave->reason }}</textarea>
                        </div>
                        <small class="text-muted">Current reason provided</small>
                    </div>

                    <!-- File Attachment -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="files" class="form-label">
                            Attachment
                            <i class="bi bi-info-circle text-muted ms-1"
                               data-bs-toggle="tooltip"
                               title="Upload new file or keep existing"></i>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-paperclip"></i></span>
                            <input type="file" name="files" id="files" class="form-control"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>

                        @if($leave->files)
                        <div class="mt-3">
                            <label class="form-label small">Current Attachment:</label>
                            <div class="d-flex align-items-center gap-2 p-2 border rounded bg-light">
                                <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                                <div class="flex-grow-1">
                                    <a href="{{ asset($leave->files) }}"
                                       target="_blank"
                                       class="text-decoration-none d-block">
                                        View Current File
                                    </a>
                                    <small class="text-muted d-block">Uploaded on {{ \Carbon\Carbon::parse($leave->created_at)->format('d M, Y') }}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="removeCurrentFile()"
                                        data-bs-toggle="tooltip"
                                        title="Remove current file">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <input type="hidden" name="remove_existing_file" id="removeExistingFile" value="0">
                        </div>
                        @endif

                        <small class="text-muted mt-2 d-block">Supported formats: PDF, DOC, JPG, PNG (Max: 5MB)</small>
                    </div>
                </div>

                <!-- Leave Information Summary -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Current Leave Details</h6>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <span class="text-muted">Employee:</span><br>
                                <strong>{{ $leave->user?->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-md-3 mb-2">
                                <span class="text-muted">Duration:</span><br>
                                <strong>{{ ucfirst(str_replace('-', ' ', $leave->duration)) }}</strong>
                            </div>
                            <div class="col-md-3 mb-2">
                                <span class="text-muted">Applied On:</span><br>
                                <strong>{{ \Carbon\Carbon::parse($leave->created_at)->format('d M, Y h:i A') }}</strong>
                            </div>
                            <div class="col-md-3 mb-2">
                                <span class="text-muted">Last Updated:</span><br>
                                <strong>{{ \Carbon\Carbon::parse($leave->updated_at)->format('d M, Y h:i A') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <div>
                        <span class="text-muted small">All fields marked with <span class="text-danger">*</span> are required</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('leaves.index') }}" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                        <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset Changes
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Update Leave
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Warning Alert -->
    @if($leave->status == 'approved')
    <div class="alert alert-warning alert-dismissible fade show mt-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div>
                <h5 class="alert-heading mb-2">Important Notice</h5>
                <p class="mb-0">This leave has already been <strong>approved</strong>. Making changes may affect payroll calculations and require re-approval.</p>
            </div>
        </div>
    </div>
    @endif
</div>

@push('css')
<style>
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    .input-group-text {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        min-width: 45px;
        justify-content: center;
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .card {
        border-radius: 0.5rem;
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    .btn {
        padding: 0.5rem 1rem;
        font-weight: 500;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    small.text-muted {
        font-size: 0.85rem;
    }
    .badge.bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    .alert {
        border-radius: 0.5rem;
    }
</style>
@endpush

@push('js')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Set today's date as min for future dates
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date')?.setAttribute('min', today);
    document.getElementById('start_date')?.setAttribute('min', today);
    document.getElementById('end_date')?.setAttribute('min', today);
});

function toggleDateFields(value) {
    const singleDate = document.getElementById('single-date');
    const multiDate = document.getElementById('multi-date');
    const dateInput = document.getElementById('date');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    if (value === 'multiple') {
        singleDate.classList.add('d-none');
        multiDate.classList.remove('d-none');

        // Expand multi-date section
        singleDate.classList.remove('col-lg-4', 'col-md-6');
        multiDate.classList.add('col-lg-8', 'col-md-12');

        // Set required attributes
        if (dateInput) dateInput.required = false;
        if (startDateInput) startDateInput.required = true;
        if (endDateInput) endDateInput.required = true;

        // Clear single date if switching to multiple
        if (dateInput) dateInput.value = '';
    } else {
        singleDate.classList.remove('d-none');
        multiDate.classList.add('d-none');

        // Reset widths
        singleDate.classList.add('col-lg-4', 'col-md-6');
        multiDate.classList.remove('col-lg-8', 'col-md-12');

        // Set required attributes
        if (dateInput) dateInput.required = true;
        if (startDateInput) startDateInput.required = false;
        if (endDateInput) endDateInput.required = false;

        // Clear multi dates if switching to single
        if (startDateInput) startDateInput.value = '';
        if (endDateInput) endDateInput.value = '';
    }
}

// End date validation
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            if (this.value) {
                endDateInput.min = this.value;
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
            }
        });

        endDateInput.addEventListener('change', function() {
            if (this.value && startDateInput.value && this.value < startDateInput.value) {
                alert('End date cannot be earlier than start date.');
                this.value = startDateInput.value;
            }
        });
    }
});

// Form validation
const form = document.getElementById('editLeaveForm');
if (form) {
    form.addEventListener('submit', function(e) {
        const duration = document.getElementById('duration').value;
        let isValid = true;
        let errorMessage = '';

        if (!duration) {
            errorMessage = 'Please select a duration.';
            isValid = false;
        } else if (duration === 'multiple') {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                errorMessage = 'Please select both start and end dates for multiple days leave.';
                isValid = false;
            } else if (startDate > endDate) {
                errorMessage = 'End date cannot be earlier than start date.';
                isValid = false;
            }
        } else {
            const singleDate = document.getElementById('date').value;
            if (!singleDate) {
                errorMessage = 'Please select a date for your leave.';
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault();
            showAlert(errorMessage, 'danger');
        }
    });
}

function showAlert(message, type) {
    // Remove existing alerts
    const existingAlert = document.querySelector('.alert.alert-dismissible:not(.alert-danger)');
    if (existingAlert) existingAlert.remove();

    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show mt-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi ${type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'} me-3 fs-4"></i>
                <div>${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alertHtml);

    // Auto remove after 5 seconds
    setTimeout(() => {
        const newAlert = document.querySelector('.alert.alert-dismissible');
        if (newAlert) newAlert.remove();
    }, 5000);
}

function removeCurrentFile() {
    if (confirm('Are you sure you want to remove the current file? This action cannot be undone.')) {
        document.getElementById('removeExistingFile').value = '1';
        document.querySelector('[for="files"]').closest('.mb-4').querySelector('.mt-3').style.display = 'none';
        showAlert('Current file will be removed on save.', 'warning');
    }
}

function resetForm() {
    if (confirm('Are you sure you want to reset all changes? This will revert to original values.')) {
        location.reload();
    }
}

// Auto-set end date based on start date for better UX
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            if (this.value && !endDateInput.value) {
                // Auto-set end date to same as start date if not set
                endDateInput.value = this.value;
            }
        });
    }
});
</script>
@endpush

@endsection
