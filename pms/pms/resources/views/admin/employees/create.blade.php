@extends('admin.layout.app')

@section('content')
<div class="container mt-4">
    <h4">{{ isset($employee) ? 'Edit Employee' : 'Add Employee' }}</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="employeeForm" action="{{ isset($employee) ? route('employees.update', $employee->id) : route('employees.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($employee))
            @method('PUT')
        @endif

        <h5 class="mb-3">Account Details</h5>

        <div class="row">
            {{-- Employee ID --}}
            <div class="col-md-4 mb-3">
                <label class="d-block">Employee ID <sup class="text-danger">*</sup></label>

                @php
                    $empOption = old('employee_id_option') ?? ((isset($employee) && $employee?->employeeDetail?->employee_id) ? 'custom' : 'auto');
                    // nextEmployeeId expected from controller (preview only)
                @endphp

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="employee_id_option" id="emp_auto" value="auto" {{ $empOption === 'auto' ? 'checked' : '' }}>
                    <label class="form-check-label" for="emp_auto">Auto-generate</label>
                </div>

                <div class="form-check form-check-inline d-none">
                    <input class="form-check-input" type="radio" name="employee_id_option" id="emp_custom" value="custom" {{ $empOption === 'custom' ? 'checked' : '' }}>
                    <label class="form-check-label" for="emp_custom">Custom ID</label>
                </div>

                <input type="text" id="employee_id_input" name="employee_id" class="form-control mt-2 readonly-like-normal"
                       placeholder="e.g. BBH2025001"
                       value="{{ old('employee_id') ?? ($employee?->employeeDetail?->employee_id ?? ($nextEmployeeId ?? '')) }}"
                       @if(!isset($employee)) readonly @endif>

                @error('employee_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Employee Name --}}
            <div class="col-md-4 mb-3">
                <label>Employee Name <sup class="text-danger">*</sup></label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') ?? $employee?->name ?? '' }}">
            </div>

            {{-- Email --}}
            <div class="col-md-4 mb-3">
                <label>Email <sup class="text-danger">*</sup></label>
                <input type="email" name="email" class="form-control" required value="{{ old('email') ?? $employee?->email ?? '' }}">
            </div>

            {{-- Password --}}
            <div class="col-md-4 mb-3">
                <label for="password">Password</label>
                <div class="input-group">
                    <!-- password is optional now; admin may set it or let user reset -->
                    <input type="password" name="password" id="password" class="form-control" autocomplete="off" minlength="8">
                    <button type="button" class="btn btn-outline-secondary toggle-password" title="Show/Hide Password">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary generate-password" title="Generate Random Password">
                        <i class="fa fa-random"></i>
                    </button>
                </div>
                
                @error('password')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror

                <!--<div class="form-check mt-2">-->
                <!--    <input class="form-check-input" type="checkbox" value="1" id="send_reset" name="send_reset" checked>-->
                <!--    <label class="form-check-label" for="send_reset">-->
                <!--        Send password reset link (recommended)-->
                <!--    </label>-->
                <!--</div>-->
            </div>

            {{-- Designation --}}
            <div class="col-md-4 mb-3">
                <label>Designation <sup class="text-danger">*</sup></label>

                @php
                    $selectedDesignation = old('designation_id') ?? ($employee?->employeeDetail?->designation_id ?? null);
                @endphp

                <div class="input-group">
                    <select name="designation_id" id="designation_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation->id }}" {{ $selectedDesignation == $designation->id ? 'selected' : '' }}>
                                {{ $designation->name }} @if(!empty($designation->unique_code)) ({{ $designation->unique_code }}) @endif
                            </option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-outline-secondary" id="openDesignationModalBtn" title="Add designation">
                        Add
                    </button>
                </div>

                @if($designations->isEmpty())
                    <div class="mt-2 p-2" style="background:#fff3cd; border:1px solid #ffeeba; border-radius:4px; color:#856404;">
                        No designations found. Click Add to create the first designation.
                    </div>
                @endif
            </div>

            {{-- Department (previously Parent Department) --}}
            <div class="col-md-4 mb-3">
                <label>Department <sup class="text-danger">*</sup></label>
                <div class="input-group">
                    @php 
                        $selectedPrt = old('parent_dpt_id') ?? ($employee?->employeeDetail?->parent_dpt_id ?? ''); 
                    @endphp

                    <select name="parent_dpt_id" id="prt_department_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($prtdepartments as $dept)
                            <option value="{{ $dept->id }}" {{ $selectedPrt == $dept->id ? 'selected' : '' }}>
                                {{ $dept->dpt_name }} @if(!empty($dept->dpt_code)) ({{ $dept->dpt_code }}) @endif
                            </option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-outline-secondary" id="openPrtModalBtn" title="Add department group">
                        Add
                    </button>
                </div>
            </div>

            {{-- Sub Department --}}
            <div class="col-md-4 mb-3">
                <label>Sub Department</label>
                <div class="input-group">
                    @php 
                        $selectedDpt = old('department_id') ?? ($employee?->employeeDetail?->department_id ?? ''); 
                    @endphp

                    <select name="department_id" id="department_id" class="form-control" data-selected="{{ $selectedDpt }}">
                        <option value="">Select</option>
                    </select>

                    <button type="button" class="btn btn-outline-secondary" id="openDptModalBtn" title="Add department">
                        Add
                    </button>
                </div>
            </div>

            {{-- Profile Picture --}}
            <div class="col-md-4 mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control">
                @if(isset($employee) && $employee?->profile_image)
                    <small class="d-block mt-1">Current: <a href="{{ asset($employee->profile_image) }}" target="_blank">view</a></small>
                @endif
            </div>

            {{-- Country --}}
            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold">Country <sup class="text-danger">*</sup></label>
                <select name="country" id="country" class="form-select form-select-sm select2">
                    <option value="">Select Country</option>
                    @php $selectedCountry = old('country') ?? ($employee?->country ?? 'India'); @endphp
                    @foreach($countries as $country)
                        <option value="{{ $country->name }}" data-flag="{{ $country->flag_url }}" {{ $selectedCountry == $country->name ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Mobile (fixed +91 prefix) --}}
            <div class="col-md-4 mb-3">
                <label>Mobile <sup class="text-danger">*</sup></label>
                <div class="input-group">
                  <span class="input-group-text">+91</span>
                  <input id="mobile_only_digits" type="text" name="mobile" class="form-control" required maxlength="10" placeholder="9876543210"
                         value="{{ old('mobile') ?? ($employee?->mobile ? preg_replace('/^\+91/', '', $employee->mobile) : '') }}">
                </div>
                <!--<small class="form-text text-muted">Enter 10 digits, do not start with 0. Country code +91 is fixed.</small>-->
                <input type="hidden" name="mobile_with_code" id="mobile_with_code" value="{{ old('mobile_with_code') ?? ($employee?->mobile ?? '') }}">
            </div>

            {{-- Gender --}}
            <div class="col-md-4 mb-3">
                <label>Gender</label>
                @php $gender = old('gender') ?? ($employee?->gender ?? '') @endphp
                <select name="gender" class="form-control">
                    <option value="">Select</option>
                    <option value="Male" {{ $gender === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ $gender === 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ $gender === 'Other' ? 'selected' : '' }}>Other</option>
                    <option value="Prefer not to say" {{ $gender === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                </select>
            </div>

            {{-- Joining Date --}}
            <div class="col-md-4 mb-3">
                <label>Joining Date <sup class="text-danger">*</sup></label>
                <input type="date" required class="form-control" name="joining_date" id="joining_date" autocomplete="off" value="{{ old('joining_date') ?? ($employee?->employeeDetail?->joining_date?->format('Y-m-d') ?? date('Y-m-d')) }}">
            </div>

            {{-- Date of Birth --}}
           <div class="col-md-4 mb-3">
    <label>Date of Birth <sup class="text-danger">*</sup> <span class="text-muted">(As per government ID)</span></label>
    <input type="date" name="dob" id="dob" class="form-control" required
           value="{{ old('dob') ?? ($employee?->dob ?? '') }}">
    @error('dob')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>


            {{-- Reporting To --}}
            <div class="col-md-4 mb-3">
                <label>Reporting To</label>
                @php $selectedReporting = old('reporting_to') ?? ($employee?->employeeDetail?->reporting_to ?? ''); @endphp
                <select name="reporting_to" class="form-control">
                    <option value="">Select</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $selectedReporting == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Language (static English) --}}
            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold">Language</label>
                <input type="text" class="form-control" value="English" readonly>
                <input type="hidden" name="language" value="en">
            </div>

            {{-- User Role --}}
            <div class="col-md-4 mb-3">
                <label>User Role <sup class="text-danger">*</sup></label>
                @php $role = old('user_role') ?? ($employee?->role ?? '') @endphp
                <select name="user_role" class="form-control select-picker" required>
                    <option value="">Select Role</option>
                    <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>Employee</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            {{-- Address --}}
            <div class="col-md-6 mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control">{{ old('address') ?? ($employee?->address ?? '') }}</textarea>
            </div>

            {{-- About --}}
            <div class="col-md-6 mb-3">
                <label>About</label>
                <textarea name="about" class="form-control">{{ old('about') ?? ($employee?->about ?? '') }}</textarea>
            </div>

            {{-- Login Allowed --}}
            <div class="col-md-6 mb-3">
                <label>Login Allowed?</label>
                @php $loginAllowed = old('login_allowed') ?? ($employee?->login_allowed ?? '1'); @endphp
                <select name="login_allowed" class="form-control">
                    <option value="1" {{ $loginAllowed == '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ $loginAllowed == '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            {{-- Email Notifications --}}
            <div class="col-md-6 mb-3">
                <label>Email Notifications?</label>
                @php $emailNotif = old('email_notifications') ?? ($employee?->email_notifications ?? '1'); @endphp
                <select name="email_notifications" class="form-control">
                    <option value="1" {{ $emailNotif == '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ $emailNotif == '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            {{-- Slack Member ID --}}


            {{-- Skills --}}
            <div class="col-md-4 mb-3">
                <label>Skills</label>
                <textarea name="skills" class="form-control">{{ old('skills') ?? ($employee?->skills ?? '') }}</textarea>
            </div>

            {{-- Employment Type --}}
            <div class="col-md-4 mb-3">
                <label for="employment_type" class="form-label">Employment Type</label>
                @php $employmentType = old('employment_type') ?? ($employee?->employment_type ?? '') @endphp
                <select name="employment_type" id="employment_type" class="form-control select-picker" data-size="8">
                    <option value="">Select</option>
                    <option value="full_time" {{ $employmentType === 'full_time' ? 'selected' : '' }}>Full Time</option>
                    <option value="part_time" {{ $employmentType === 'part_time' ? 'selected' : '' }}>Part Time</option>
                    <option value="on_contract" {{ $employmentType === 'on_contract' ? 'selected' : '' }}>On Contract</option>
                    <option value="internship" {{ $employmentType === 'internship' ? 'selected' : '' }}>Internship</option>
                    <option value="trainee" {{ $employmentType === 'trainee' ? 'selected' : '' }}>Trainee</option>
                </select>
            </div>

            {{-- Marital Status --}}
            <div class="col-md-4 mb-3">
                <label for="marital_status" class="form-label">Marital Status</label>
                @php $marital = old('marital_status') ?? ($employee?->marital_status ?? '') @endphp
                <select name="marital_status" id="marital_status" class="form-control select-picker" data-size="8">
                    <option value="">Select</option>
                    <option value="single" {{ $marital === 'single' ? 'selected' : '' }}>Single</option>
                    <option value="married" {{ $marital === 'married' ? 'selected' : '' }}>Married</option>
                    <option value="widower" {{ $marital === 'widower' ? 'selected' : '' }}>Widower</option>
                    <option value="widow" {{ $marital === 'widow' ? 'selected' : '' }}>Widow</option>
                    <option value="separate" {{ $marital === 'separate' ? 'selected' : '' }}>Separate</option>
                    <option value="divorced" {{ $marital === 'divorced' ? 'selected' : '' }}>Divorced</option>
                    <option value="engaged" {{ $marital === 'engaged' ? 'selected' : '' }}>Engaged</option>
                </select>
            </div>

            {{-- Business Address --}}
            <div class="col-md-4 mb-3">
                <label>Business Address <sup class="text-danger">*</sup></label>
                <textarea name="business_address" class="form-control" required>{{ old('business_address') ?? ($employee?->employeeDetail?->business_address ?? 'Kolkata') }}</textarea>
            </div>

            {{-- Status / Exit Date --}}
            <div class="col-md-4 mb-3 d-flex align-items-center">
                <label class="me-3 mb-0" style="min-width: 70px;">Status <sup class="text-danger">*</sup></label>
                @php $status = old('status') ?? ($employee?->employeeDetail?->status ?? 'Active') @endphp
                <div class="d-flex gap-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status-active" value="Active" {{ $status === 'Active' ? 'checked' : '' }} onchange="toggleExitDate()">
                        <label class="form-check-label" for="status-active">Active</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status-inactive" value="Inactive" {{ $status === 'Inactive' ? 'checked' : '' }} onchange="toggleExitDate()">
                        <label class="form-check-label" for="status-inactive">Inactive</label>
                    </div>
                </div>
            </div>

            <div class="col-md-4" id="exit-date-container">
                <label>Exit Date</label>
                <input type="date" name="exit_date" id="exit_date" class="form-control" value="{{ old('exit_date') ?? ($employee?->employeeDetail?->exit_date ?? '') }}">
            </div>
        </div>

        <div class="text-start mt-3">
            <button type="submit" class="btn btn-primary">{{ isset($employee) ? 'Update' : 'Save' }}</button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    {{-- Parent Department Modal --}}
    <div class="modal fade" id="prtModal" tabindex="-1" aria-labelledby="prtModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="addPrtDptForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Parent Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <table class="table table-bordered mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Parent Department Name</th>
                                    <th width="120">Action</th>
                                </tr>
                            </thead>
                            <tbody id="prt-dpt-list">
                                @foreach($prtdepartments as $index => $prt)
                                    <tr id="prt-row-{{ $prt->id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $prt->dpt_name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger delete-prt" data-id="{{ $prt->id }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mb-3">
                            <label>Parent Department Name <sup class="text-danger">*</sup></label>
                            <input type="text" name="dpt_name" id="prt_dpt_name" class="form-control" required>
                            <div id="prt-group-error" class="text-danger d-none mt-2"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Department Modal --}}
    <div class="modal fade" id="dptModal" tabindex="-1" aria-labelledby="dptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="addDptForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <table class="table table-bordered mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Parent Department Name</th>
                                    <th>Department Name</th>
                                    <th width="120">Action</th>
                                </tr>
                            </thead>
                            <tbody id="dpt-list">
                                @foreach($departments as $index => $dpt)
                                    <tr id="dpt-row-{{ $dpt->id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $dpt->parent?->dpt_name ?? 'N/A' }}</td>
                                        <td>{{ $dpt->dpt_name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger delete-dpt" data-id="{{ $dpt->id }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mb-3">
                            <label>Parent Department (optional)</label>
                            <select name="parent_dpt_id" id="dpt_parent_select" class="form-control">
                                <option value="">None</option>
                                @foreach($prtdepartments as $pd)
                                    <option value="{{ $pd->id }}">{{ $pd->dpt_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Department Name <sup class="text-danger">*</sup></label>
                            <input type="text" name="dpt_name" id="dpt_name" class="form-control" required>
                            <div id="dpt-group-error" class="text-danger d-none mt-2"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Designation Modal --}}
    <div class="modal fade" id="designationModal" tabindex="-1" aria-labelledby="designationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addDesignationForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Designation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($designations->isNotEmpty())
                            <div class="mb-3">
                                <label class="form-label">Existing Designations</label>
                                <ul class="list-group mb-2">
                                    @foreach($designations as $des)
                                        <li class="list-group-item small">{{ $des->name }} @if($des->unique_code) ({{ $des->unique_code }}) @endif</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label>Designation Name <sup class="text-danger">*</sup></label>
                            <input type="text" name="name" id="designationName" class="form-control" required>
                            <div class="text-danger mt-2 d-none" id="designation-error"></div>
                        </div>

                        <div class="mb-3">
                            <label>Parent (optional)</label>
                            <select name="parent_id" id="designation_parent" class="form-control">
                                <option value="">None</option>
                                @foreach($designations as $des)
                                    <option value="{{ $des->id }}">{{ $des->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="status" value="Active">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveDesignationBtn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css" />
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
/* make disabled/readonly inputs match normal inputs visually */
input[disabled],
input[readonly] {
  background-color: #fff !important;
  opacity: 1 !important;
  color: #212529 !important;
}

/* subtle border tweak to match other inputs */
input.readonly-like-normal {
  border-color: #d1d5db;
  box-shadow: none;
}
</style>
@endpush

@push('js')
<script>
    // Utility: safe get element
    const $el = id => document.getElementById(id);

    function toggleExitDate() {
        const isInactive = $el('status-inactive') && $el('status-inactive').checked;
        const container = $el('exit-date-container');
        if (container) container.style.display = isInactive ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize exit date visibility
        toggleExitDate();

        // Employee ID auto/custom logic
        const empAuto = $el('emp_auto');
        const empCustom = $el('emp_custom'); // hidden option
        const empInput = $el('employee_id_input');
        const nextId = {!! json_encode($nextEmployeeId ?? '') !!};

        function updateEmpInputState() {
            const isCustom = empCustom && empCustom.checked;

            if (!empInput) return;

            empInput.readOnly = !isCustom;
            empInput.required = !!isCustom;

            if (!isCustom) {
                // show preview next id when auto selected (if provided)
                if (nextId) empInput.value = nextId;
                empInput.classList.add('readonly-like-normal');
            } else {
                empInput.classList.remove('readonly-like-normal');
            }
        }

        if (empAuto) empAuto.addEventListener('change', updateEmpInputState);
        if (empCustom) empCustom.addEventListener('change', updateEmpInputState);
        updateEmpInputState();

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function () {
                const passwordField = $el('password');
                const icon = this.querySelector('i');
                if (!passwordField) return;
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordField.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Generate random password
        document.querySelectorAll('.generate-password').forEach(btn => {
            btn.addEventListener('click', function () {
                const passwordField = $el('password');
                if (!passwordField) return;
                const randomPassword = Math.random().toString(36).slice(-10) + '!A1';
                passwordField.value = randomPassword;
            });
        });

        // UX: show hint/placeholder depending on send_reset checkbox
        const sendResetCheckbox = $el('send_reset');
        if (sendResetCheckbox) {
            const passwordField = $el('password');
            function togglePasswordField() {
                if (!passwordField) return;
                if (sendResetCheckbox.checked) {
                    passwordField.placeholder = 'Optional — user will receive a reset link';
                } else {
                    passwordField.placeholder = 'Set a password (will be emailed)';
                }
            }
            sendResetCheckbox.addEventListener('change', togglePasswordField);
            togglePasswordField();
        }

        // Open modals
        const desBtn = $el('openDesignationModalBtn');
        if (desBtn) desBtn.addEventListener('click', function () {
            const modal = new bootstrap.Modal($el('designationModal'));
            modal.show();
        });

        const prtBtn = $el('openPrtModalBtn');
        if (prtBtn) prtBtn.addEventListener('click', function () {
            const modal = new bootstrap.Modal($el('prtModal'));
            modal.show();
        });

        const dptBtn = $el('openDptModalBtn');
        if (dptBtn) dptBtn.addEventListener('click', function () {
            const modal = new bootstrap.Modal($el('dptModal'));
            modal.show();
        });

        // Mobile input: only digits, limit 10, strip leading zero
        const mobileOnly = $el('mobile_only_digits');
        if (mobileOnly) {
            mobileOnly.addEventListener('input', function (e) {
                let v = this.value.replace(/\D/g, '').slice(0, 10);
                if (v.length > 0 && v[0] === '0') v = v.replace(/^0+/, '');
                this.value = v;
            });
        }

        // On submit prepare full mobile with +91 prefix and basic client validations
        const form = $el('employeeForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                // DOB required
                const dobEl = $el('dob');
                if (dobEl && !dobEl.value) {
                    e.preventDefault();
                    dobEl.focus();
                    alert('Please provide Date of Birth (DOB). It is required.');
                    return false;
                }

                // ensure mobile is valid
                const mobileEl = $el('mobile_only_digits');
                if (mobileEl) {
                    const m = mobileEl.value.trim();
                    if (!/^[1-9]\d{9}$/.test(m)) {
                        e.preventDefault();
                        mobileEl.focus();
                        alert('Please enter a valid 10-digit mobile number (no leading 0).');
                        return false;
                    }
                    // set hidden full mobile field
                    const hidden = $el('mobile_with_code');
                    if (hidden) hidden.value = '+91' + m;
                }

                // No client-side checks for probation/notice since removed from UX
            });
        }
    });
</script>

<script>
    // jQuery helpers and AJAX
    (function($){
        // Parent -> Sub Department dynamic logic
        function loadSubDepartments(parentId, selectedId = null) {
            const $sub = $('#department_id');
            $sub.empty().append('<option value="">Select</option>');

            if (!parentId) {
                return;
            }

            let url = '{{ route("employees.sub-departments", ":id") }}';
            url = url.replace(':id', parentId);

            $.get(url)
             .done(function (data) {
                if (!Array.isArray(data)) {
                    console.error('Unexpected sub-departments response:', data);
                    return;
                }
                data.forEach(function (dept) {
                    const isSelected = selectedId && parseInt(selectedId) === parseInt(dept.id);
                    let text = dept.dpt_name;
                    if (dept.dpt_code) {
                        text += ' (' + dept.dpt_code + ')';
                    }
                    $sub.append(
                        $('<option>', {
                            value: dept.id,
                            text: text,
                            selected: isSelected
                        })
                    );
                });
             })
                          .fail(function (xhr, status, err) {
                console.error('Failed to load sub-departments', status, err);
             });
        }

        $(document).ready(function () {
            const initialParent = $('#prt_department_id').val();
            const selectedSub   = $('#department_id').data('selected');

            if (initialParent) {
                loadSubDepartments(initialParent, selectedSub);
            }

            $('#prt_department_id').on('change', function () {
                const parentId = $(this).val();
                loadSubDepartments(parentId, null);
            });
        });

        // Parent Department add
        $('#addPrtDptForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const data = $form.serialize();

            $.ajax({
                url: '{{ route('parent-departments.store') }}',
                method: 'POST',
                data: data,
                success: function(res) {
                    if (res.status === 'success' && res.dpt) {
                        $('#prt_department_id').append(`<option value="${res.dpt.id}" selected>${res.dpt.dpt_name}</option>`);
                        $('#prt-dpt-list').append(`
                            <tr id="prt-row-${res.dpt.id}">
                                <td>#</td>
                                <td>${res.dpt.dpt_name}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger delete-prt" data-id="${res.dpt.id}">Delete</button>
                                </td>
                            </tr>
                        `);
                        $form[0].reset();
                        $('#prtModal').modal('hide');
                    } else {
                        $('#prt-group-error').removeClass('d-none').text(res.message || 'Something went wrong.');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : 'Error occurred.');
                    $('#prt-group-error').removeClass('d-none').text(msg);
                }
            });
        });

        // Delete parent department
        $(document).on('click', '.delete-prt', function () {
            const id = $(this).data('id');
            if (!confirm('Are you sure you want to delete this parent department?')) return;

            $.ajax({
                url: `{{ url('parent-departments') }}/${id}`,
                method: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        $(`#prt-row-${id}`).remove();
                        $(`#prt_department_id option[value="${id}"]`).remove();
                    }
                }
            });
        });

        // Department add
        $('#addDptForm').on('submit', function(e) {
            e.preventDefault();
            const data = $(this).serialize();

            $.ajax({
                url: '{{ route('departments.store') }}',
                method: 'POST',
                data: data,
                success: function(res) {
                    if (res.status === 'success' && res.dpt) {
                        const currentParent = $('#prt_department_id').val();
                        loadSubDepartments(currentParent, res.dpt.id);

                        $('#dpt-list').append(`
                            <tr id="dpt-row-${res.dpt.id}">
                                <td>#</td>
                                <td>${res.dpt.parent_name ? res.dpt.parent_name : 'N/A'}</td>
                                <td>${res.dpt.dpt_name}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger delete-dpt" data-id="${res.dpt.id}">Delete</button>
                                </td>
                            </tr>
                        `);
                        $('#addDptForm')[0].reset();
                        $('#dptModal').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Added', text: 'Department added successfully', timer: 1400, showConfirmButton: false });
                    } else {
                        $('#dpt-group-error').removeClass('d-none').text(res.message || 'Error occurred.');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : 'Error occurred.');
                    $('#dpt-group-error').removeClass('d-none').text(msg);
                }
            });
        });

        // Delete department
        $(document).on('click', '.delete-dpt', function () {
            const id = $(this).data('id');
            if (!confirm('Are you sure you want to delete this department?')) return;

            $.ajax({
                url: `{{ url('departments') }}/${id}`,
                method: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        $(`#dpt-row-${id}`).remove();
                        $(`#department_id option[value="${id}"]`).remove();
                    }
                }
            });
        });

        // Designation add (AJAX)
        $('#addDesignationForm').on('submit', function (e) {
            e.preventDefault();

            const $btn = $('#saveDesignationBtn');
            const name = $('#designationName').val().trim();
            const parent_id = $('#designation_parent').val() || null;
            const token = '{{ csrf_token() }}';

            $('#designation-error').addClass('d-none').text('');

            if (!name) {
                $('#designation-error').removeClass('d-none').text('Please enter a designation name.');
                return;
            }

            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route('designations.ajax.store') }}',
                method: 'POST',
                data: { _token: token, name: name, parent_id: parent_id, status: 'Active' },
                success: function (res) {
                    if (res.designation && res.designation.id) {
                        const label = res.designation.unique_code ? `${res.designation.name} (${res.designation.unique_code})` : res.designation.name;

                        $('#designation_id').append(`<option value="${res.designation.id}" selected>${label}</option>`);

                        $('#addDesignationForm')[0].reset();
                        $('#designationModal').modal('hide');

                        Swal.fire({ icon: 'success', title: 'Created', text: 'Designation created successfully', timer: 1400, showConfirmButton: false });
                    } else {
                        $('#designation-error').removeClass('d-none').text(res.message || 'Designation created but unexpected response.');
                    }
                },
                error: function (xhr) {
                    let msg = 'Something went wrong';
                    if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                    else if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    $('#designation-error').removeClass('d-none').text(msg);
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });
    })(jQuery);
</script>

<script>
$(document).ready(function () {
    function formatCountry (state) {
        if (!state.id) return state.text;
        let flag = $(state.element).data("flag");
        if (flag) {
            return $('<span><img src="' + flag + '" width="20" class="me-2"/> ' + state.text + '</span>');
        }
        return state.text;
    }

    $('#country').select2({
        theme: "bootstrap-5",
        templateResult: formatCountry,
        templateSelection: formatCountry,
        placeholder: "Select Country",
        allowClear: true
    });

    // Ensure India is the first and selected option client-side if server didn't already put it first
    const $country = $('#country');
    const indiaOption = $country.find('option').filter(function(){ return $(this).text().trim() === 'India'; }).first();
    if (indiaOption.length) {
        // Move India to top and select it if no selection made
        $country.prepend(indiaOption);
        if (!$country.val()) $country.val(indiaOption.val()).trigger('change.select2');
    }
});
</script>

<script>
try {
  if (typeof flatpickr !== 'undefined') {
    const commonOpts = { dateFormat: "Y-m-d", allowInput: true, altInput: true, altFormat: "d-m-Y" };

    if (document.getElementById('dob')) flatpickr("#dob", commonOpts);
    if (document.getElementById('joining_date')) flatpickr("#joining_date", commonOpts);
  }
} catch (e) { console.warn('flatpickr init error', e); }
</script>
@endpush

