@extends('admin.layout.app')

@section('title', 'Apply Leave')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-2">Apply for Leave</h1>
            <p class="text-muted mb-0">Submit a new leave request for review</p>
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
            <h5 class="card-title mb-0"><i class="bi bi-calendar-plus me-2"></i>Leave Application Form</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data" id="leaveForm">
                @csrf

                <div class="row">
                    <!-- Employee Selection -->
                    @if(auth()->user()->role === 'admin')
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="user_id" class="form-label">
                            Employee <span class="text-danger">*</span>
                            <i class="bi bi-info-circle text-muted ms-1"
                               data-bs-toggle="tooltip"
                               title="Select employee for whom leave is being applied"></i>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->designation ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">Select the employee requesting leave</small>
                    </div>
                    @else
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label class="form-label">Employee</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control bg-light"
                                   value="{{ auth()->user()->name }} ({{ auth()->user()->designation ?? 'N/A' }})"
                                   readonly>
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                        </div>
                        <small class="text-muted">Your employee information</small>
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
                                <option value="sick" {{ old('type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="casual" {{ old('type') == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                                <option value="leave-without-pay" {{ old('type') == 'leave-without-pay' ? 'selected' : '' }}>Leave Without Pay</option>
                            </select>
                        </div>
                        <small class="text-muted">Select the type of leave you're applying for</small>
                    </div>

                    <!-- Status (Admin Only) -->
                    @if(auth()->user()->role === 'admin')
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="status" class="form-label">Status</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-check-circle"></i></span>
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            </select>
                        </div>
                        <small class="text-muted">Set initial leave status</small>
                    </div>
                    @endif

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
                                <option value="full-day" {{ old('duration') == 'full-day' ? 'selected' : '' }}>Full Day</option>
                                <option value="multiple" {{ old('duration') == 'multiple' ? 'selected' : '' }}>Multiple Days</option>
                                <option value="first-half" {{ old('duration') == 'first-half' ? 'selected' : '' }}>First Half</option>
                                <option value="second-half" {{ old('duration') == 'second-half' ? 'selected' : '' }}>Second Half</option>
                            </select>
                        </div>
                        <small class="text-muted">Select the duration of your leave</small>
                    </div>

                    <!-- Single Date Field -->
                    <div class="col-lg-4 col-md-6 mb-4" id="single-date">
                        <label for="date" class="form-label">
                            Date <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                            <input type="date" name="date" id="date" class="form-control"
                                   value="{{ old('date') }}"
                                   min="{{ date('Y-m-d') }}">
                        </div>
                        <small class="text-muted">Select the leave date</small>
                    </div>

                    <!-- Multi Date Fields (Hidden by Default) -->
                    <div class="col-lg-8 col-md-12 mb-4 d-none" id="multi-date">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-plus"></i></span>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                           value="{{ old('start_date') }}"
                                           min="{{ date('Y-m-d') }}">
                                </div>
                                <small class="text-muted">First day of leave</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-minus"></i></span>
                                    <input type="date" name="end_date" id="end_date" class="form-control"
                                           value="{{ old('end_date') }}"
                                           min="{{ date('Y-m-d') }}">
                                </div>
                                <small class="text-muted">Last day of leave</small>
                            </div>
                        </div>
                    </div>

                    <!-- Reason for Absence -->
                    <div class="col-lg-8 col-md-12 mb-4">
                        <label for="reason" class="form-label">
                            Reason for Absence <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                            <textarea name="reason" id="reason" class="form-control" rows="3"
                                      placeholder="Please provide details for your leave request..."
                                      required>{{ old('reason') }}</textarea>
                        </div>
                        <small class="text-muted">Provide a brief description for your leave</small>
                    </div>

                    <!-- File Attachment -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="files" class="form-label">
                            Attachment
                            <i class="bi bi-info-circle text-muted ms-1"
                               data-bs-toggle="tooltip"
                               title="Optional: Upload supporting documents (medical certificate, etc.)"></i>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-paperclip"></i></span>
                            <input type="file" name="files" id="files" class="form-control"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                        <small class="text-muted">Supported formats: PDF, DOC, JPG, PNG (Max: 5MB)</small>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <div>
                        <span class="text-muted small">All fields marked with <span class="text-danger">*</span> are required</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset
                        </button>
                        <a href="{{ route('leaves.index') }}" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-check me-2"></i>Submit Leave Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Card -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-light py-3">
            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Guidelines</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary mb-2"><i class="bi bi-clock-history me-2"></i>Leave Types</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1"><strong>Sick Leave:</strong> For medical reasons with supporting documents</li>
                        <li class="mb-1"><strong>Casual Leave:</strong> For personal reasons requiring prior approval</li>
                        <li><strong>Leave Without Pay:</strong> Unpaid leave for extended absence</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-2"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1">Submit leave requests at least 2 days in advance</li>
                        <li class="mb-1">Attach supporting documents for sick leave</li>
                        <li>Check your leave balance before applying</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
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
});

function toggleDateFields(value) {
    const singleDate = document.getElementById('single-date');
    const multiDate = document.getElementById('multi-date');
    const dateInput = document.getElementById('date');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    // Clear all date inputs
    if (dateInput) dateInput.value = '';
    if (startDateInput) startDateInput.value = '';
    if (endDateInput) endDateInput.value = '';

    if (value === 'multiple') {
        singleDate.classList.add('d-none');
        multiDate.classList.remove('d-none');

        // Expand multi-date to full width
        singleDate.classList.remove('col-lg-4', 'col-md-6');
        multiDate.classList.add('col-lg-8', 'col-md-12');
    } else {
        singleDate.classList.remove('d-none');
        multiDate.classList.add('d-none');

        // Reset widths
        singleDate.classList.add('col-lg-4', 'col-md-6');
        multiDate.classList.remove('col-lg-8', 'col-md-12');
    }
}

// Set minimum dates to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];

    // Set min dates for all date inputs
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.min = today;
    });

    // Initialize date fields based on saved value
    const durationSelect = document.getElementById('duration');
    if (durationSelect && durationSelect.value) {
        toggleDateFields(durationSelect.value);
    }

    // End date validation
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
    }

    // Form validation
    const form = document.getElementById('leaveForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const duration = document.getElementById('duration').value;
            let isValid = true;

            if (duration === 'multiple') {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;

                if (!startDate || !endDate) {
                    alert('Please select both start and end dates for multiple days leave.');
                    isValid = false;
                } else if (startDate > endDate) {
                    alert('End date cannot be earlier than start date.');
                    isValid = false;
                }
            } else {
                const singleDate = document.getElementById('date').value;
                if (!singleDate) {
                    alert('Please select a date for your leave.');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});

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
