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
                        <div class="invalid-feedback employee-id-error d-none">Please enter a valid employee ID</div>
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
                        <div class="invalid-feedback name-error d-none">Please enter the employee's full name</div>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control email-input" required
                               value="{{ old('email') ?? $employee?->email ?? '' }}"
                               placeholder="employee@company.com">
                        <div class="invalid-feedback email-error d-none">Please enter a valid email address</div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control password-input"
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
                        <div class="invalid-feedback password-error d-none">Password must be at least 8 characters</div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Designation -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Designation <span class="text-danger">*</span></label>
                        @php $selectedDesignation = old('designation_id') ?? ($employee?->employeeDetail?->designation_id ?? null); @endphp
                        <div class="input-group">
                            <select name="designation_id" id="designation_id" class="form-select designation-select" required>
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
                        <small class="text-muted">Select existing or add new (will be saved with employee)</small>
                        <div class="invalid-feedback designation-error d-none">Please select a designation</div>
                        @error('designation_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                        @php $selectedPrt = old('parent_dpt_id') ?? ($employee?->employeeDetail?->parent_dpt_id ?? ''); @endphp
                        <div class="input-group">
                            <select name="parent_dpt_id" id="prt_department_id" class="form-select department-select" required>
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
                        <small class="text-muted">Select existing or add new (will be saved with employee)</small>
                        <div class="invalid-feedback department-error d-none">Please select a department</div>
                        @error('parent_dpt_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Sub Department -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Sub Department</label>
                        @php $selectedDpt = old('department_id') ?? ($employee?->employeeDetail?->department_id ?? ''); @endphp
                        <div class="input-group">
                            <select name="department_id" id="department_id" class="form-select subdepartment-select" data-selected="{{ $selectedDpt }}">
                                <option value="">Select Sub Department</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="openDptModalBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <small class="text-muted">Select existing or add new (will be saved with employee)</small>
                    </div>

                    <!-- Profile Picture - FIXED WITH MESSAGE -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control profile-input" accept="image/*">

                        <!-- ADDED: Message about allowed image extensions -->
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1 text-info"></i>
                            Allowed formats: JPG, JPEG, PNG, GIF, WEBP, SVG
                        </small>
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-file-archive me-1 text-info"></i>
                            Maximum file size: 2MB
                        </small>

                        @if(isset($employee) && $employee?->profile_image)
                            <div class="mt-2 border rounded p-2 bg-light">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset($employee->profile_image) }}" alt="Current Profile" class="rounded-circle me-3" width="60" height="60">
                                    <div>
                                        <small class="d-block text-muted fw-medium">Current profile picture</small>
                                        <small class="d-block text-muted">Will be replaced if you upload a new one</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Country -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Country <span class="text-danger">*</span></label>
                        <select name="country" id="country" class="form-select select2 country-select">
                            <option value="">Select Country</option>
                            @php $selectedCountry = old('country') ?? ($employee?->country ?? 'India'); @endphp
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" data-flag="{{ $country->flag_url }}" {{ $selectedCountry == $country->name ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback country-error d-none">Please select a country</div>
                        @error('country')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Mobile - COMPLETELY FIXED SECTION -->
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
                        <div id="mobile-error" class="invalid-feedback mobile-error d-none"></div>
                        <input type="hidden" name="mobile_with_code" id="mobile_with_code" value="{{ old('mobile_with_code') ?? ($employee?->mobile ?? '') }}">
                        @error('mobile')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Gender</label>
                        @php $gender = old('gender') ?? ($employee?->gender ?? '') @endphp
                        <select name="gender" class="form-select gender-select">
                            <option value="">Select Gender</option>
                            <option value="Male" {{ $gender === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ $gender === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ $gender === 'Other' ? 'selected' : '' }}>Other</option>
                            <option value="Prefer not to say" {{ $gender === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                        </select>
                        @error('gender')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Joining Date - FIXED -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Joining Date <span class="text-danger">*</span></label>
                        <input type="date" required class="form-control joining-date-input" name="joining_date" id="joining_date"
                               value="{{ old('joining_date') ?? ($employee?->employeeDetail?->joining_date?->format('Y-m-d') ?? date('Y-m-d')) }}">
                        <small class="text-muted d-block mt-1">Date cannot be in the past</small>
                        <div class="invalid-feedback joining-date-error d-none">Please select a valid joining date</div>
                        @error('joining_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Date of Birth -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="dob" id="dob" class="form-control dob-input" required
                               value="{{ old('dob') ?? ($employee?->dob ?? '') }}">
                        <small class="text-muted">As per government ID</small>
                        <div class="invalid-feedback dob-error d-none">Please select a valid date of birth</div>
                        @error('dob')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reporting To -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Reporting To</label>
                        @php $selectedReporting = old('reporting_to') ?? ($employee?->employeeDetail?->reporting_to ?? ''); @endphp
                        <select name="reporting_to" class="form-select reporting-select">
                            <option value="">Select Reporting Manager</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $selectedReporting == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('reporting_to')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
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
                        <select name="user_role" class="form-select role-select" required>
                            <option value="">Select Role</option>
                            <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        <div class="invalid-feedback role-error d-none">Please select a user role</div>
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
                        <textarea name="address" class="form-control address-input" rows="3" placeholder="Enter complete address">{{ old('address') ?? ($employee?->address ?? '') }}</textarea>
                        @error('address')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- About -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-medium">About</label>
                        <textarea name="about" class="form-control about-input" rows="3" placeholder="About the employee">{{ old('about') ?? ($employee?->about ?? '') }}</textarea>
                        @error('about')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Login Allowed -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Allow Login? <span class="text-danger">*</span></label>
                        @php $loginAllowed = old('login_allowed') ?? ($employee?->login_allowed ?? '1'); @endphp
                        <select name="login_allowed" class="form-select login-allowed-select" required>
                            <option value="1" {{ $loginAllowed == '1' ? 'selected' : '' }}>Yes - Can login to system</option>
                            <option value="0" {{ $loginAllowed == '0' ? 'selected' : '' }}>No - Cannot login</option>
                        </select>
                        <small class="text-muted">Employee can only login if this is "Yes" AND Status is "Active"</small>
                        <div class="invalid-feedback login-allowed-error d-none">Please select login permission</div>
                        @error('login_allowed')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Notifications -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Email Notifications?</label>
                        @php $emailNotif = old('email_notifications') ?? ($employee?->email_notifications ?? '1'); @endphp
                        <select name="email_notifications" class="form-select email-notif-select">
                            <option value="1" {{ $emailNotif == '1' ? 'selected' : '' }}>Yes - Receive emails</option>
                            <option value="0" {{ $emailNotif == '0' ? 'selected' : '' }}>No - No emails</option>
                        </select>
                        @error('email_notifications')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Skills -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Skills</label>
                        <textarea name="skills" class="form-control skills-input" rows="3" placeholder="e.g. PHP, Laravel, JavaScript">{{ old('skills') ?? ($employee?->skills ?? '') }}</textarea>
                        @error('skills')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Employment Type -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Employment Type <span class="text-danger">*</span></label>
                        @php $employmentType = old('employment_type') ?? ($employee?->employment_type ?? '') @endphp
                        <select name="employment_type" id="employment_type" class="form-select employment-type-select" required>
                            <option value="">Select Type</option>
                            <option value="full_time" {{ $employmentType === 'full_time' ? 'selected' : '' }}>Full Time</option>
                            <option value="part_time" {{ $employmentType === 'part_time' ? 'selected' : '' }}>Part Time</option>
                            <option value="on_contract" {{ $employmentType === 'on_contract' ? 'selected' : '' }}>On Contract</option>
                            <option value="internship" {{ $employmentType === 'internship' ? 'selected' : '' }}>Internship</option>
                            <option value="trainee" {{ $employmentType === 'trainee' ? 'selected' : '' }}>Trainee</option>
                        </select>
                        <div class="invalid-feedback employment-type-error d-none">Please select employment type</div>
                        @error('employment_type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Marital Status -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Marital Status</label>
                        @php $marital = old('marital_status') ?? ($employee?->marital_status ?? '') @endphp
                        <select name="marital_status" id="marital_status" class="form-select marital-status-select">
                            <option value="">Select Status</option>
                            <option value="single" {{ $marital === 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married" {{ $marital === 'married' ? 'selected' : '' }}>Married</option>
                            <option value="widower" {{ $marital === 'widower' ? 'selected' : '' }}>Widower</option>
                            <option value="widow" {{ $marital === 'widow' ? 'selected' : '' }}>Widow</option>
                            <option value="separate" {{ $marital === 'separate' ? 'selected' : '' }}>Separate</option>
                            <option value="divorced" {{ $marital === 'divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="engaged" {{ $marital === 'engaged' ? 'selected' : '' }}>Engaged</option>
                        </select>
                        @error('marital_status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Business Address -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-medium">Business Address <span class="text-danger">*</span></label>
                        <textarea name="business_address" class="form-control business-address-input" required>{{ old('business_address') ?? ($employee?->employeeDetail?->business_address ?? 'Kolkata') }}</textarea>
                        <div class="invalid-feedback business-address-error d-none">Please enter business address</div>
                        @error('business_address')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Status Section -->
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
                        <div class="d-flex gap-3 status-radio-group">
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
                        <div class="invalid-feedback status-error d-none">Please select employment status</div>
                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Exit Date (Conditional) -->
                    <div class="col-md-4 mb-3" id="exit-date-container" style="display: {{ $status === 'Inactive' ? 'block' : 'none' }};">
                        <label class="form-label fw-medium">Exit Date</label>
                        <input type="date" name="exit_date" id="exit_date" class="form-control exit-date-input"
                               value="{{ old('exit_date') ?? ($employee?->employeeDetail?->exit_date ?? '') }}">
                        <!-- ADDED: Message about exit date login logic -->
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-clock me-1 text-warning"></i>
                            Employee can login UP TO this date. Login will be blocked AFTER this date.
                        </small>
                        <div class="invalid-feedback exit-date-error d-none">Please select a valid exit date</div>
                        @error('exit_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
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

<!-- Modals (same as before) -->
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
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }

    .is-valid {
        border-color: #198754 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
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

    /* Error message styling */
    .invalid-feedback {
        display: none;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }

    .invalid-feedback.d-block {
        display: block !important;
    }

    .invalid-feedback.d-none {
        display: none !important;
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

    // Clear all error messages on input
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').addClass('d-none').removeClass('d-block');

        // Special handling for radio buttons
        if ($(this).is(':radio')) {
            const name = $(this).attr('name');
            $(`input[name="${name}"]`).removeClass('is-invalid');
            $(`.${name}-error`).addClass('d-none').removeClass('d-block');
        }

        // For mobile input specifically
        if ($(this).attr('id') === 'mobile_only_digits') {
            $('#mobile-error').addClass('d-none').removeClass('d-block');
        }
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

    // Mobile number validation - FIXED VERSION
    $('#mobile_only_digits').on('input', function(e) {
        let value = $(this).val().replace(/\D/g, '').slice(0, 10);
        if (value.length > 0 && value[0] === '0') {
            value = value.replace(/^0+/, '');
        }
        $(this).val(value);

        // Clear error message on input
        $('#mobile-error').addClass('d-none').removeClass('d-block').text('');
        $(this).removeClass('error valid is-invalid');
    });

    // Check mobile uniqueness on blur - FIXED VERSION
    let mobileCheckAjax = null;
    $('#mobile_only_digits').on('blur', function() {
        const value = $(this).val().trim();
        const employeeId = $(this).data('current-id') || null;

        // Clear any existing error
        $('#mobile-error').addClass('d-none').removeClass('d-block').text('');
        $(this).removeClass('error valid is-invalid');

        // Only validate if field is not empty
        if (value.length === 0) {
            $(this).removeClass('error valid is-invalid');
            return;
        }

        // Format validation
        if (!/^[1-9]\d{9}$/.test(value)) {
            // Invalid mobile number
            $(this).addClass('is-invalid');
            $('#mobile-error').text('Please enter a valid 10-digit mobile number').removeClass('d-none').addClass('d-block');
            return;
        }

        // Valid format - update hidden field
        $('#mobile_with_code').val('+91' + value);
        $(this).addClass('valid').removeClass('is-invalid');

        // Check uniqueness via AJAX
        if (mobileCheckAjax) {
            mobileCheckAjax.abort();
        }

        mobileCheckAjax = $.ajax({
            url: '{{ route("employees.check-mobile") }}',
            method: 'POST',
            data: {
                mobile: value,
                employee_id: employeeId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.exists) {
                    $('#mobile_only_digits').addClass('is-invalid').removeClass('valid');
                    $('#mobile-error').text('This mobile number is already registered').removeClass('d-none').addClass('d-block');
                } else {
                    $('#mobile_only_digits').removeClass('is-invalid').addClass('valid');
                    $('#mobile-error').addClass('d-none').removeClass('d-block').text('');
                }
            },
            error: function(xhr, status, error) {
                // Only show error if it's not an abort
                if (status !== 'abort') {
                    console.error('Mobile check error:', error);
                    // DON'T show error to user - just log it
                    // The mobile number is already validated for format
                    $('#mobile_only_digits').removeClass('is-invalid');
                    $('#mobile-error').addClass('d-none').removeClass('d-block').text('');
                }
            }
        });
    });

    // Email validation
    $('.email-input').on('blur', function() {
        const email = $(this).val().trim();
        if (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $(this).addClass('is-invalid');
                $(this).next('.email-error').text('Please enter a valid email address').removeClass('d-none').addClass('d-block');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.email-error').addClass('d-none').removeClass('d-block');
            }
        }
    });

    // Password validation
    $('.password-input').on('blur', function() {
        const password = $(this).val().trim();
        if (password && password.length > 0) {
            if (password.length < 8) {
                $(this).addClass('is-invalid');
                $(this).next('.password-error').removeClass('d-none').addClass('d-block');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.password-error').addClass('d-none').removeClass('d-block');
            }
        }
    });

    // Date validation
    $('.dob-input').on('blur', function() {
        const dob = $(this).val();
        if (dob) {
            const dobDate = new Date(dob);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Date of birth must be in the past
            if (dobDate >= today) {
                $(this).addClass('is-invalid');
                $(this).next('.dob-error').text('Date of birth must be in the past').removeClass('d-none').addClass('d-block');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.dob-error').addClass('d-none').removeClass('d-block');
            }
        }
    });

    // Joining Date validation - Allow today and future, not past
    $('.joining-date-input').on('blur', function() {
        const joiningDate = $(this).val();
        if (joiningDate) {
            const joinDate = new Date(joiningDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Set to start of day

            // Joining date CANNOT be before today
            if (joinDate < today) {
                $(this).addClass('is-invalid');
                $(this).next('.joining-date-error').text('Joining date cannot be in the past').removeClass('d-none').addClass('d-block');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.joining-date-error').addClass('d-none').removeClass('d-block');
            }
        }
    });

    // Exit date validation - Employee can login UP TO exit date
    $('.exit-date-input').on('blur', function() {
        const exitDate = $(this).val();
        if (exitDate) {
            const exit = new Date(exitDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Exit date can be any date (past, present, or future)
            // Logic is handled in controller
            if (exit < today) {
                // If exit date is in the past, show warning
                Swal.fire({
                    icon: 'warning',
                    title: 'Exit Date in Past',
                    text: 'Employee will not be able to login as exit date has already passed.',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
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

    // Save new designation to hidden field
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

    // Save new department to hidden field
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

    // Save new sub department to hidden field
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

        // Clear all previous validation
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').addClass('d-none').removeClass('d-block');

        // Validate required fields
        $(this).find('[required]').each(function() {
            // Skip hidden fields
            if ($(this).is(':hidden')) return;

            let value = $(this).val();
            let isRadio = $(this).is(':radio');
            let isCheckbox = $(this).is(':checkbox');

            if (isRadio || isCheckbox) {
                // For radio/checkbox, check if any in group is checked
                const name = $(this).attr('name');
                const isChecked = $(`input[name="${name}"]:checked`).length > 0;
                if (!isChecked) {
                    $(`input[name="${name}"]`).first().addClass('is-invalid');
                    $(`.${name}-error`).removeClass('d-none').addClass('d-block');
                    isValid = false;
                    if (!firstError) firstError = $(`input[name="${name}"]`).first();
                }
            } else if ($(this).is('select')) {
                if (!value || value === '') {
                    $(this).addClass('is-invalid');
                    $(this).next(`.${$(this).attr('class').split(' ').find(c => c.includes('-select'))}-error`).removeClass('d-none').addClass('d-block');
                    isValid = false;
                    if (!firstError) firstError = $(this);
                }
            } else {
                // Handle input fields
                if (!value || value.toString().trim() === '') {
                    $(this).addClass('is-invalid');
                    $(this).next(`.${$(this).attr('class').split(' ').find(c => c.includes('-input'))}-error`).removeClass('d-none').addClass('d-block');
                    isValid = false;
                    if (!firstError) firstError = $(this);
                }
            }
        });

        // Validate email format if provided
        const email = $('input[name="email"]').val().trim();
        if (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $('input[name="email"]').addClass('is-invalid');
                $('.email-error').text('Please enter a valid email address').removeClass('d-none').addClass('d-block');
                isValid = false;
                if (!firstError) firstError = $('input[name="email"]');
            }
        }

        // Validate mobile format if provided
        const mobile = $('#mobile_only_digits').val().trim();
        if (mobile) {
            if (!/^[1-9]\d{9}$/.test(mobile)) {
                $('#mobile_only_digits').addClass('is-invalid');
                $('#mobile-error').text('Please enter a valid 10-digit mobile number').removeClass('d-none').addClass('d-block');
                isValid = false;
                if (!firstError) firstError = $('#mobile_only_digits');
            }
        }

        // Validate password if provided
        const password = $('#password').val();
        if (password && password.length > 0) {
            if (password.length < 8) {
                $('#password').addClass('is-invalid');
                $('.password-error').removeClass('d-none').addClass('d-block');
                isValid = false;
                if (!firstError) firstError = $('#password');
            }
        }

        // Validate date fields
        const dob = $('#dob').val();
        if (dob) {
            const dobDate = new Date(dob);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Date of birth must be in the past
            if (dobDate >= today) {
                $('#dob').addClass('is-invalid');
                $('.dob-error').text('Date of birth must be in the past').removeClass('d-none').addClass('d-block');
                isValid = false;
                if (!firstError) firstError = $('#dob');
            }
        }

        const joiningDate = $('#joining_date').val();
        if (joiningDate) {
            const joinDate = new Date(joiningDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Joining date cannot be in the past
            if (joinDate < today) {
                $('#joining_date').addClass('is-invalid');
                $('.joining-date-error').text('Joining date cannot be in the past').removeClass('d-none').addClass('d-block');
                isValid = false;
                if (!firstError) firstError = $('#joining_date');
            }
        }

        // Validate exit date if status is inactive
        if ($('#status-inactive').is(':checked')) {
            const exitDate = $('#exit_date').val();
            if (!exitDate) {
                $('#exit_date').addClass('is-invalid');
                $('.exit-date-error').text('Exit date is required for inactive status').removeClass('d-none').addClass('d-block');
                isValid = false;
                if (!firstError) firstError = $('#exit_date');
            }
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

        // Update mobile with code if valid
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
