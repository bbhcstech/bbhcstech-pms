@extends('admin.layout.app')

@section('content')
<div class="container-fluid mt-4 px-3 px-md-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 fw-bold text-primary mb-1">
                <i class="fas fa-user-plus me-2"></i>{{ isset($employee) ? 'Edit Employee' : 'Add New Employee' }}
            </h2>
            <p class="text-muted mb-0">Fill in the employee details below</p>
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Error Alert -->
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Main Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Employee Information</h5>
        </div>

        <form id="employeeForm" action="{{ isset($employee) ? route('employees.update', $employee->id) : route('employees.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            @if(isset($employee))
                @method('PUT')
            @endif

            <!-- Hidden fields for new designation/department -->
            <input type="hidden" name="new_designation" id="new_designation" value="">
            <input type="hidden" name="new_department" id="new_department" value="">
            <input type="hidden" name="new_sub_department" id="new_sub_department" value="">

            <div class="card-body">
                <!-- Account Details Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fas fa-user-circle me-2"></i>Account Details
                        </h6>
                    </div>

                    <!-- Employee ID -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Employee ID <span class="text-danger">*</span></label>
                        @php
                            $empOption = old('employee_id_option') ?? ((isset($employee) && $employee?->employeeDetail?->employee_id) ? 'custom' : 'auto');
                        @endphp

                        <div class="mb-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employee_id_option" id="emp_auto" value="auto" {{ $empOption === 'auto' ? 'checked' : '' }}>
                                <label class="form-check-label" for="emp_auto">Auto-generate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employee_id_option" id="emp_custom" value="custom" {{ $empOption === 'custom' ? 'checked' : '' }}>
                                <label class="form-check-label" for="emp_custom">Custom ID</label>
                            </div>
                        </div>

                        <input type="text" id="employee_id_input" name="employee_id" class="form-control"
                               placeholder="e.g. BBH2025001"
                               value="{{ old('employee_id') ?? ($employee?->employeeDetail?->employee_id ?? ($nextEmployeeId ?? '')) }}"
                               @if($empOption === 'auto') readonly @endif>
                        <small class="text-muted">Employee ID will be auto-generated</small>
                        @error('employee_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Employee Name -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="{{ old('name') ?? $employee?->name ?? '' }}"
                               placeholder="Enter full name">
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required
                               value="{{ old('email') ?? $employee?->email ?? '' }}"
                               placeholder="employee@company.com">
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control"
                                   autocomplete="new-password" minlength="8"
                                   placeholder="Leave blank for auto-generate">
                            <button type="button" class="btn btn-outline-secondary toggle-password" title="Show/Hide">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary generate-password" title="Generate">
                                <i class="fas fa-random"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum 8 characters. Leave empty for auto-generation.</small>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Designation -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Designation <span class="text-danger">*</span></label>
                        @php $selectedDesignation = old('designation_id') ?? ($employee?->employeeDetail?->designation_id ?? null); @endphp
                        <div class="input-group">
                            <select name="designation_id" id="designation_id" class="form-select" required>
                                <option value="">Select Designation</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}" {{ $selectedDesignation == $designation->id ? 'selected' : '' }}>
                                        {{ $designation->name }} @if(!empty($designation->unique_code)) ({{ $designation->unique_code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="openDesignationModalBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="form-text">Select existing or add new (will be saved with employee)</div>
                        @error('designation_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                        @php $selectedPrt = old('parent_dpt_id') ?? ($employee?->employeeDetail?->parent_dpt_id ?? ''); @endphp
                        <div class="input-group">
                            <select name="parent_dpt_id" id="prt_department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach($prtdepartments as $dept)
                                    <option value="{{ $dept->id }}" {{ $selectedPrt == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->dpt_name }} @if(!empty($dept->dpt_code)) ({{ $dept->dpt_code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="openPrtModalBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="form-text">Select existing or add new (will be saved with employee)</div>
                        @error('parent_dpt_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Sub Department -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Sub Department</label>
                        @php $selectedDpt = old('department_id') ?? ($employee?->employeeDetail?->department_id ?? ''); @endphp
                        <div class="input-group">
                            <select name="department_id" id="department_id" class="form-select" data-selected="{{ $selectedDpt }}">
                                <option value="">Select Sub Department</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="openDptModalBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="form-text">Select existing or add new (will be saved with employee)</div>
                    </div>

                    <!-- Profile Picture -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control" accept="image/*">
                        @if(isset($employee) && $employee?->profile_image)
                            <div class="mt-2">
                                <img src="{{ asset($employee->profile_image) }}" alt="Current Profile" class="rounded-circle" width="60" height="60">
                                <small class="d-block text-muted mt-1">Current profile picture</small>
                            </div>
                        @endif
                    </div>

                    <!-- Country -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Country <span class="text-danger">*</span></label>
                        <select name="country" id="country" class="form-select select2">
                            <option value="">Select Country</option>
                            @php $selectedCountry = old('country') ?? ($employee?->country ?? 'India'); @endphp
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" data-flag="{{ $country->flag_url }}" {{ $selectedCountry == $country->name ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Mobile - FIXED SECTION -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Mobile Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">+91</span>
                            <input id="mobile_only_digits" type="text" name="mobile" class="form-control mobile-input" required
                                   maxlength="10" placeholder="9876543210"
                                   value="{{ old('mobile') ?? ($employee?->mobile ? preg_replace('/^\+91/', '', $employee->mobile) : '') }}"
                                   @if(isset($employee)) data-current-id="{{ $employee->id }}" @endif>
                        </div>
                        <small class="text-muted">Enter 10-digit mobile number without 0 or +91</small>
                        <div id="mobile-error" class="text-danger small mt-1"></div>
                        <input type="hidden" name="mobile_with_code" id="mobile_with_code" value="{{ old('mobile_with_code') ?? ($employee?->mobile ?? '') }}">
                        @error('mobile')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Gender</label>
                        @php $gender = old('gender') ?? ($employee?->gender ?? '') @endphp
                        <select name="gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="Male" {{ $gender === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ $gender === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ $gender === 'Other' ? 'selected' : '' }}>Other</option>
                            <option value="Prefer not to say" {{ $gender === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                        </select>
                    </div>

                    <!-- Joining Date -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Joining Date <span class="text-danger">*</span></label>
                        <input type="date" required class="form-control" name="joining_date" id="joining_date"
                               value="{{ old('joining_date') ?? ($employee?->employeeDetail?->joining_date?->format('Y-m-d') ?? date('Y-m-d')) }}">
                        @error('joining_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Date of Birth -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="dob" id="dob" class="form-control" required
                               value="{{ old('dob') ?? ($employee?->dob ?? '') }}">
                        <small class="text-muted">As per government ID</small>
                        @error('dob')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reporting To -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Reporting To</label>
                        @php $selectedReporting = old('reporting_to') ?? ($employee?->employeeDetail?->reporting_to ?? ''); @endphp
                        <select name="reporting_to" class="form-select">
                            <option value="">Select Reporting Manager</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $selectedReporting == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Language -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Language</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="English" readonly>
                            <span class="input-group-text"><i class="fas fa-globe"></i></span>
                        </div>
                        <input type="hidden" name="language" value="en">
                    </div>

                    <!-- User Role -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">User Role <span class="text-danger">*</span></label>
                        @php $role = old('user_role') ?? ($employee?->role ?? '') @endphp
                        <select name="user_role" class="form-select" required>
                            <option value="">Select Role</option>
                            <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('user_role')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fas fa-info-circle me-2"></i>Additional Information
                        </h6>
                    </div>

                    <!-- Address -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-medium">Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Enter complete address">{{ old('address') ?? ($employee?->address ?? '') }}</textarea>
                    </div>

                    <!-- About -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-medium">About</label>
                        <textarea name="about" class="form-control" rows="3" placeholder="About the employee">{{ old('about') ?? ($employee?->about ?? '') }}</textarea>
                    </div>

                    <!-- Login Allowed - IMPORTANT: Controls login access -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Allow Login? <span class="text-danger">*</span></label>
                        @php $loginAllowed = old('login_allowed') ?? ($employee?->login_allowed ?? '1'); @endphp
                        <select name="login_allowed" class="form-select" required>
                            <option value="1" {{ $loginAllowed == '1' ? 'selected' : '' }}>Yes - Can login to system</option>
                            <option value="0" {{ $loginAllowed == '0' ? 'selected' : '' }}>No - Cannot login</option>
                        </select>
                        <small class="text-muted">Employee can only login if this is "Yes" AND Status is "Active"</small>
                    </div>

                    <!-- Email Notifications -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Email Notifications?</label>
                        @php $emailNotif = old('email_notifications') ?? ($employee?->email_notifications ?? '1'); @endphp
                        <select name="email_notifications" class="form-select">
                            <option value="1" {{ $emailNotif == '1' ? 'selected' : '' }}>Yes - Receive emails</option>
                            <option value="0" {{ $emailNotif == '0' ? 'selected' : '' }}>No - No emails</option>
                        </select>
                    </div>

                    <!-- Skills -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Skills</label>
                        <textarea name="skills" class="form-control" rows="3" placeholder="e.g. PHP, Laravel, JavaScript">{{ old('skills') ?? ($employee?->skills ?? '') }}</textarea>
                    </div>

                    <!-- Employment Type -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Employment Type <span class="text-danger">*</span></label>
                        @php $employmentType = old('employment_type') ?? ($employee?->employment_type ?? '') @endphp
                        <select name="employment_type" id="employment_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="full_time" {{ $employmentType === 'full_time' ? 'selected' : '' }}>Full Time</option>
                            <option value="part_time" {{ $employmentType === 'part_time' ? 'selected' : '' }}>Part Time</option>
                            <option value="on_contract" {{ $employmentType === 'on_contract' ? 'selected' : '' }}>On Contract</option>
                            <option value="internship" {{ $employmentType === 'internship' ? 'selected' : '' }}>Internship</option>
                            <option value="trainee" {{ $employmentType === 'trainee' ? 'selected' : '' }}>Trainee</option>
                        </select>
                        @error('employment_type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Marital Status -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Marital Status</label>
                        @php $marital = old('marital_status') ?? ($employee?->marital_status ?? '') @endphp
                        <select name="marital_status" id="marital_status" class="form-select">
                            <option value="">Select Status</option>
                            <option value="single" {{ $marital === 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married" {{ $marital === 'married' ? 'selected' : '' }}>Married</option>
                            <option value="widower" {{ $marital === 'widower' ? 'selected' : '' }}>Widower</option>
                            <option value="widow" {{ $marital === 'widow' ? 'selected' : '' }}>Widow</option>
                            <option value="separate" {{ $marital === 'separate' ? 'selected' : '' }}>Separate</option>
                            <option value="divorced" {{ $marital === 'divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="engaged" {{ $marital === 'engaged' ? 'selected' : '' }}>Engaged</option>
                        </select>
                    </div>

                    <!-- Business Address -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Business Address <span class="text-danger">*</span></label>
                        <textarea name="business_address" class="form-control" required>{{ old('business_address') ?? ($employee?->employeeDetail?->business_address ?? 'Kolkata') }}</textarea>
                        @error('business_address')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Status Section - IMPORTANT: Controls employment status -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fas fa-user-check me-2"></i>Employment Status
                        </h6>
                    </div>

                    <!-- Status -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Employment Status <span class="text-danger">*</span></label>
                        @php $status = old('status') ?? ($employee?->employeeDetail?->status ?? 'Active') @endphp
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status-active" value="Active" {{ $status === 'Active' ? 'checked' : '' }} onchange="toggleExitDate()">
                                <label class="form-check-label text-success fw-medium" for="status-active">
                                    <i class="fas fa-circle-check me-1"></i>Active
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status-inactive" value="Inactive" {{ $status === 'Inactive' ? 'checked' : '' }} onchange="toggleExitDate()">
                                <label class="form-check-label text-danger fw-medium" for="status-inactive">
                                    <i class="fas fa-circle-xmark me-1"></i>Inactive
                                </label>
                            </div>
                        </div>
                        <small class="text-muted">Employee can only login if this is "Active" AND Login Allowed is "Yes"</small>
                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Exit Date (Conditional) -->
                    <div class="col-md-4 mb-3" id="exit-date-container" style="display: {{ $status === 'Inactive' ? 'block' : 'none' }};">
                        <label class="form-label fw-medium">Exit Date</label>
                        <input type="date" name="exit_date" id="exit_date" class="form-control"
                               value="{{ old('exit_date') ?? ($employee?->employeeDetail?->exit_date ?? '') }}">
                    </div>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="card-footer bg-light border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Fields marked with <span class="text-danger">*</span> are required
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>{{ isset($employee) ? 'Update Employee' : 'Save Employee' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Designation Modal -->
<div class="modal fade" id="designationModal" tabindex="-1" aria-labelledby="designationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="designationModalLabel">Add New Designation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Designation Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="designationName" placeholder="Enter designation name" required>
                    <div class="invalid-feedback" id="designation-error"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Parent Designation (Optional)</label>
                    <select id="designation_parent" class="form-select">
                        <option value="">None</option>
                        @foreach($designations as $des)
                            <option value="{{ $des->id }}">{{ $des->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveDesignationBtn">Add Designation</button>
            </div>
        </div>
    </div>
</div>

<!-- Department Modal -->
<div class="modal fade" id="prtModal" tabindex="-1" aria-labelledby="prtModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prtModalLabel">Add New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Department Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="prt_dpt_name" placeholder="Enter department name" required>
                    <div class="invalid-feedback" id="prt-group-error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePrtDepartmentBtn">Add Department</button>
            </div>
        </div>
    </div>
</div>

<!-- Sub Department Modal -->
<div class="modal fade" id="dptModal" tabindex="-1" aria-labelledby="dptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dptModalLabel">Add New Sub Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Parent Department</label>
                    <select id="dpt_parent_select" class="form-select">
                        <option value="">None</option>
                        @foreach($prtdepartments as $pd)
                            <option value="{{ $pd->id }}">{{ $pd->dpt_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sub Department Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="dpt_name" placeholder="Enter sub department name" required>
                    <div class="invalid-feedback" id="dpt-group-error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveSubDepartmentBtn">Add Sub Department</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    /* Enhanced UI Styling */
    .card {
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }

    .form-label {
        color: #495057;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
    }

    .btn-outline-primary {
        border-color: #0d6efd;
        color: #0d6efd;
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 500;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    /* Radio button styling */
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    /* Validation styling */
    .is-invalid {
        border-color: #dc3545 !important;
    }

    .is-valid {
        border-color: #198754 !important;
    }

    .mobile-input.error {
        border-color: #dc3545 !important;
    }

    .mobile-input.valid {
        border-color: #198754 !important;
    }

    /* Section headers */
    h6.fw-bold.text-primary {
        position: relative;
        padding-left: 1.5rem;
    }

    h6.fw-bold.text-primary::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 8px;
        height: 20px;
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
        border-radius: 4px;
    }

    /* Select2 customization */
    .select2-container .select2-selection--single {
        height: 48px;
        border-radius: 8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 48px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.25rem;
        }

        .form-control, .form-select {
            font-size: 16px;
        }
    }

    /* Animation for success messages */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert {
        animation: fadeIn 0.3s ease;
    }
</style>
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: "bootstrap-5",
        width: '100%'
    });

    // Toggle exit date based on status
    function toggleExitDate() {
        const isInactive = $('#status-inactive').is(':checked');
        $('#exit-date-container').toggle(isInactive);
        if (isInactive) {
            $('#exit_date').prop('required', true);
        } else {
            $('#exit_date').prop('required', false).val('');
        }
    }

    // Initialize exit date visibility
    toggleExitDate();
    $('input[name="status"]').change(toggleExitDate);

    // Employee ID auto/custom logic
    function updateEmpInputState() {
        const isCustom = $('#emp_custom').is(':checked');
        const nextId = '{{ $nextEmployeeId ?? "" }}';

        if (isCustom) {
            $('#employee_id_input').prop('readonly', false).prop('required', true);
        } else {
            $('#employee_id_input').prop('readonly', true).prop('required', false);
            if (nextId && !$('#employee_id_input').val()) {
                $('#employee_id_input').val(nextId);
            }
        }
    }

    updateEmpInputState();
    $('input[name="employee_id_option"]').change(updateEmpInputState);

    // Password visibility toggle
    $('.toggle-password').click(function() {
        const passwordField = $('#password');
        const icon = $(this).find('i');
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Generate random password
    $('.generate-password').click(function() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let password = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        $('#password').val(password);
        Swal.fire({
            icon: 'success',
            title: 'Password Generated',
            text: 'A strong password has been generated',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Mobile number validation and format
    $('#mobile_only_digits').on('input', function(e) {
        let value = $(this).val().replace(/\D/g, '').slice(0, 10);
        if (value.length > 0 && value[0] === '0') {
            value = value.replace(/^0+/, '');
        }
        $(this).val(value);
        $('#mobile-error').text('').hide();
        $(this).removeClass('error valid');
    });

    // Check mobile uniqueness on blur
    $('#mobile_only_digits').on('blur', function() {
        const value = $(this).val().trim();
        const employeeId = $(this).data('current-id') || null;

        if (value.length === 10 && /^[1-9]\d{9}$/.test(value)) {
            // Update hidden mobile field
            $('#mobile_with_code').val('+91' + value);

            // Check uniqueness via AJAX
            $.ajax({
                url: '{{ route("employees.check-mobile") }}',
                method: 'POST',
                data: {
                    mobile: value,
                    employee_id: employeeId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.exists) {
                        $('#mobile_only_digits').addClass('error').removeClass('valid');
                        $('#mobile-error').text('This mobile number is already registered').show();
                    } else {
                        $('#mobile_only_digits').removeClass('error').addClass('valid');
                        $('#mobile-error').text('').hide();
                    }
                },
                error: function() {
                    $('#mobile-error').text('Error checking mobile number').show();
                }
            });
        } else if (value.length > 0) {
            $('#mobile_only_digits').addClass('error').removeClass('valid');
            $('#mobile-error').text('Please enter a valid 10-digit mobile number').show();
        }
    });

    // Load sub-departments when parent department changes
    $('#prt_department_id').change(function() {
        const parentId = $(this).val();
        const selectedSub = $('#department_id').data('selected');

        if (!parentId) {
            $('#department_id').html('<option value="">Select Sub Department</option>');
            return;
        }

        $.ajax({
            url: '{{ route("employees.sub-departments", ":id") }}'.replace(':id', parentId),
            method: 'GET',
            success: function(data) {
                let options = '<option value="">Select Sub Department</option>';
                data.forEach(function(dept) {
                    const isSelected = selectedSub && parseInt(selectedSub) === parseInt(dept.id);
                    let text = dept.dpt_name;
                    if (dept.dpt_code) {
                        text += ' (' + dept.dpt_code + ')';
                    }
                    options += `<option value="${dept.id}" ${isSelected ? 'selected' : ''}>${text}</option>`;
                });
                $('#department_id').html(options);
            }
        });
    });

    // Initialize sub-departments if parent is selected
    if ($('#prt_department_id').val()) {
        $('#prt_department_id').trigger('change');
    }

    // Modal open handlers
    $('#openDesignationModalBtn').click(function() {
        $('#designationName').val('');
        $('#designation_parent').val('');
        $('#designationModal').modal('show');
    });

    $('#openPrtModalBtn').click(function() {
        $('#prt_dpt_name').val('');
        $('#prtModal').modal('show');
    });

    $('#openDptModalBtn').click(function() {
        $('#dpt_name').val('');
        $('#dpt_parent_select').val('');
        $('#dptModal').modal('show');
    });

    // Save new designation to hidden field (not to database yet)
    $('#saveDesignationBtn').click(function() {
        const designationName = $('#designationName').val().trim();

        if (!designationName) {
            $('#designationName').addClass('is-invalid');
            return;
        }

        // Check if designation already exists in dropdown
        let exists = false;
        $('#designation_id option').each(function() {
            if ($(this).text().toLowerCase().includes(designationName.toLowerCase())) {
                exists = true;
                return false;
            }
        });

        if (exists) {
            $('#designationName').addClass('is-invalid');
            $('#designation-error').text('This designation already exists').show();
            return;
        }

        // Store in hidden field
        $('#new_designation').val(designationName);

        // Create temporary option in select
        const tempOption = `<option value="new_designation" selected>${designationName} (New)</option>`;
        $('#designation_id').append(tempOption);

        // Close modal
        $('#designationModal').modal('hide');

        Swal.fire({
            icon: 'success',
            title: 'Designation Added',
            text: 'Designation will be saved with employee',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Save new department to hidden field (not to database yet)
    $('#savePrtDepartmentBtn').click(function() {
        const deptName = $('#prt_dpt_name').val().trim();

        if (!deptName) {
            $('#prt_dpt_name').addClass('is-invalid');
            return;
        }

        // Check if department already exists in dropdown
        let exists = false;
        $('#prt_department_id option').each(function() {
            if ($(this).text().toLowerCase().includes(deptName.toLowerCase())) {
                exists = true;
                return false;
            }
        });

        if (exists) {
            $('#prt_dpt_name').addClass('is-invalid');
            $('#prt-group-error').text('This department already exists').show();
            return;
        }

        // Store in hidden field
        $('#new_department').val(deptName);

        // Create temporary option in select
        const tempOption = `<option value="new_department" selected>${deptName} (New)</option>`;
        $('#prt_department_id').append(tempOption);

        // Close modal
        $('#prtModal').modal('hide');

        Swal.fire({
            icon: 'success',
            title: 'Department Added',
            text: 'Department will be saved with employee',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Save new sub department to hidden field (not to database yet)
    $('#saveSubDepartmentBtn').click(function() {
        const subDeptName = $('#dpt_name').val().trim();
        const parentId = $('#dpt_parent_select').val();

        if (!subDeptName) {
            $('#dpt_name').addClass('is-invalid');
            return;
        }

        // Check if sub department already exists in dropdown
        let exists = false;
        $('#department_id option').each(function() {
            if ($(this).text().toLowerCase().includes(subDeptName.toLowerCase())) {
                exists = true;
                return false;
            }
        });

        if (exists) {
            $('#dpt_name').addClass('is-invalid');
            $('#dpt-group-error').text('This sub department already exists').show();
            return;
        }

        // Store in hidden field
        $('#new_sub_department').val(subDeptName);

        // Create temporary option in select
        const tempOption = `<option value="new_sub_department" selected>${subDeptName} (New)</option>`;
        $('#department_id').append(tempOption);

        // Close modal
        $('#dptModal').modal('hide');

        Swal.fire({
            icon: 'success',
            title: 'Sub Department Added',
            text: 'Sub department will be saved with employee',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Form validation before submission
    $('#employeeForm').submit(function(e) {
        let isValid = true;
        let firstError = null;

        // Clear previous validation
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').remove();

        // Validate required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.invalid-feedback').length) {
                    $(this).after('<div class="invalid-feedback">This field is required</div>');
                }
                isValid = false;
                if (!firstError) firstError = $(this);
            }
        });

        // Validate email format
        const email = $('input[name="email"]').val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $('input[name="email"]').addClass('is-invalid');
            if (!$('input[name="email"]').next('.invalid-feedback').length) {
                $('input[name="email"]').after('<div class="invalid-feedback">Please enter a valid email address</div>');
            }
            isValid = false;
            if (!firstError) firstError = $('input[name="email"]');
        }

        // Validate mobile format
        const mobile = $('#mobile_only_digits').val().trim();
        if (mobile && !/^[1-9]\d{9}$/.test(mobile)) {
            $('#mobile_only_digits').addClass('is-invalid');
            if (!$('#mobile_only_digits').next('.invalid-feedback').length) {
                $('#mobile_only_digits').after('<div class="invalid-feedback">Please enter a valid 10-digit mobile number</div>');
            }
            isValid = false;
            if (!firstError) firstError = $('#mobile_only_digits');
        }

        // Validate password if provided
        const password = $('#password').val();
        if (password && password.length < 8) {
            $('#password').addClass('is-invalid');
            if (!$('#password').next('.invalid-feedback').length) {
                $('#password').after('<div class="invalid-feedback">Password must be at least 8 characters</div>');
            }
            isValid = false;
            if (!firstError) firstError = $('#password');
        }

        if (!isValid) {
            e.preventDefault();
            if (firstError) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
                firstError.focus();
            }
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fix all errors before submitting',
            });
            return false;
        }

        // Update mobile with code
        if (mobile && /^[1-9]\d{9}$/.test(mobile)) {
            $('#mobile_with_code').val('+91' + mobile);
        }

        // Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        return true;
    });

    // Handle modal close - clear validation
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').remove();
    });
});
</script>
@endpush
