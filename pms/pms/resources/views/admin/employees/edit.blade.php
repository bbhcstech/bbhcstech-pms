@extends('admin.layout.app')

@section('content')
<div class="container mt-4" style="background: linear-gradient(135deg, #f5e6ff 0%, #e6ccff 100%); padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <h4 style="color: #4a1c6c; font-weight: 600; margin-bottom: 20px; text-shadow: 1px 1px 2px rgba(255,255,255,0.5);">Edit Employee</h4>

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

    @php
        $ed = $employee->employeeDetail ?? null;

        function fmtDate($val) {
            if (!$val) return '';
            try {
                return \Carbon\Carbon::parse($val)->format('Y-m-d');
            } catch (\Exception $e) {
                return $val;
            }
        }
    @endphp

    <form id="employeeForm" action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data" style="background-color: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(106, 13, 173, 0.08); border: 1px solid #f0e6ff;">
        @csrf
        @method('PUT')

        <!-- Hidden fields for new designation/department -->
        <input type="hidden" name="new_designation" id="new_designation" value="">
        <input type="hidden" name="new_designation_level" id="new_designation_level" value="">
        <input type="hidden" name="new_department" id="new_department" value="">
        <input type="hidden" name="new_sub_department" id="new_sub_department" value="">

        <h5 class="mb-3" style="color: #5a2a8c; border-bottom: 2px solid #e6d0ff; padding-bottom: 10px;">Account Details</h5>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="d-block" style="color: #4a1c6c; font-weight: 500;">Employee ID <sup class="text-danger">*</sup></label>

                @php
                    $empOption = old('employee_id_option') ?? (($ed && $ed->employee_id) ? 'custom' : 'auto');
                @endphp

                <div class="form-check form-check-inline d-none">
                    <input class="form-check-input" type="radio" name="employee_id_option" id="emp_custom" value="custom" {{ $empOption === 'custom' ? 'checked' : '' }}>
                    <label class="form-check-label" for="emp_custom">Custom ID</label>
                </div>

                <input type="text" id="employee_id_input" name="employee_id" class="form-control mt-2 readonly-like-normal"
                       placeholder="e.g. BBH2025001"
                       value="{{ old('employee_id') ?? ($ed->employee_id ?? '') }}"
                       readonly>

                @error('employee_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Employee Name <sup class="text-danger">*</sup></label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') ?? ($employee->name ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Email <sup class="text-danger">*</sup></label>
                <input type="email" name="email" class="form-control" required value="{{ old('email') ?? ($employee->email ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label for="password" style="color: #4a1c6c; font-weight: 500;">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" autocomplete="off" minlength="8">
                    <button type="button" class="btn btn-outline-secondary toggle-password" title="Show/Hide Password">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary generate-password" title="Generate Random Password">
                        <i class="fa fa-random"></i>
                    </button>
                </div>
                <small class="form-text text-muted">Leave blank to keep existing password. Min 8 characters if changing.</small>
                @error('password')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Designation <sup class="text-danger">*</sup></label>
                @php $selectedDesignation = old('designation_id') ?? ($ed->designation_id ?? null); @endphp
                <div class="input-group">
                    <select name="designation_id" id="designation_id" class="form-control" required>
                        <option value="">Select Designation</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation->id }}" {{ $selectedDesignation == $designation->id ? 'selected' : '' }}>
                                {{ $designation->name }}
                                @if(!empty($designation->unique_code))
                                    ({{ $designation->unique_code }})
                                @endif
                                @if(!empty($designation->level))
                                    - Level {{ $designation->level }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-secondary" id="openDesignationModalBtn" title="Add/Edit Designation" style="border-color: #d0b0ff; color: #5a2a8c;">Manage</button>
                </div>
                @if($designations->isEmpty())
                    <div class="mt-2 p-2" style="background:#fff3cd; border:1px solid #ffeeba; border-radius:4px; color:#856404;">
                        No designations found. Click Manage to create the first designation.
                    </div>
                @endif
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Department <sup class="text-danger">*</sup></label>
                <div class="input-group">
                    @php $selectedPrt = old('parent_dpt_id') ?? ($ed->parent_dpt_id ?? ''); @endphp
                    <select name="parent_dpt_id" id="prt_department_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($prtdepartments as $dept)
                            <option value="{{ $dept->id }}" {{ $selectedPrt == $dept->id ? 'selected' : '' }}>
                                {{ $dept->dpt_name }} @if(!empty($dept->dpt_code)) ({{ $dept->dpt_code }}) @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-secondary" id="openPrtModalBtn" title="Add parent department" style="border-color: #d0b0ff; color: #5a2a8c;">Manage</button>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Sub Department</label>
                <div class="input-group">
                    @php $selectedDpt = old('department_id') ?? ($ed->department_id ?? ''); @endphp
                    <select name="department_id" id="department_id" class="form-control" data-selected="{{ $selectedDpt }}">
                        <option value="">Select</option>
                    </select>
                    <button type="button" class="btn btn-outline-secondary" id="openDptModalBtn" title="Add department" style="border-color: #d0b0ff; color: #5a2a8c;">Manage </button>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control" id="profile_picture">
                @if(!empty($employee->profile_image))
                    <small class="d-block mt-1">Current: <a href="{{ asset($employee->profile_image) }}" target="_blank">view</a></small>
                @endif
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold" style="color: #4a1c6c; font-weight: 500;">Country <sup class="text-danger">*</sup></label>
                <select name="country" id="country" class="form-select form-select-sm select2">
                    <option value="">Select Country</option>
                    @php $selectedCountry = old('country') ?? ($ed->country ?? ($employee->country ?? '')); @endphp
                    @foreach($countries as $country)
                        <option value="{{ $country->name }}" data-flag="{{ $country->flag_url }}" {{ $selectedCountry == $country->name ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Mobile <sup class="text-danger">*</sup></label>
                <div class="input-group">
                  <span class="input-group-text">+91</span>
                  <input id="mobile_only_digits" type="text" name="mobile" class="form-control" required maxlength="10" placeholder="9876543210"
                         value="{{ old('mobile') ?? ($ed->mobile ? preg_replace('/^\+91/', '', $ed->mobile) : preg_replace('/^\+91/', '', ($employee->mobile ?? '')) ) }}">
                </div>
                <input type="hidden" name="mobile_with_code" id="mobile_with_code" value="{{ old('mobile_with_code') ?? ($ed->mobile ?? $employee->mobile ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Gender</label>
                @php $gender = old('gender') ?? ($ed->gender ?? ($employee->gender ?? '')) @endphp
                <select name="gender" class="form-control">
                    <option value="">Select</option>
                    <option value="Male" {{ $gender === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ $gender === 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ $gender === 'Other' ? 'selected' : '' }}>Other</option>
                    <option value="Prefer not to say" {{ $gender === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-medium" style="color: #4a1c6c; font-weight: 500;">Joining Date <span class="text-danger">*</span></label>
                <input type="date" required class="form-control joining-date-input" name="joining_date" id="joining_date"
                       value="{{ old('joining_date') ?? (isset($ed->joining_date) ? fmtDate($ed->joining_date) : date('Y-m-d')) }}">
                <small class="text-muted d-block mt-1">Put your joining date</small>
                <div class="invalid-feedback joining-date-error d-none">Please select a valid joining date</div>
                @error('joining_date')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">
                    Date of Birth <sup class="text-danger">*</sup>
                    <span class="text-muted">(As per government ID)</span>
                </label>
                <input type="date" name="dob" id="dob" class="form-control" required
                       value="{{ old('dob') ?? fmtDate($ed->dob ?? $employee->dob ?? '') }}">
                @error('dob')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Reporting To</label>
                @php $selectedReporting = old('reporting_to') ?? ($ed->reporting_to ?? ''); @endphp
                <select name="reporting_to" class="form-control">
                    <option value="">Select</option>
                    @foreach($users as $userItem)
                        <option value="{{ $userItem->id }}" {{ (string)$selectedReporting === (string)$userItem->id ? 'selected' : '' }} {{ $userItem->id == $employee->id ? 'disabled' : '' }}>
                            {{ $userItem->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold" style="color: #4a1c6c; font-weight: 500;">Change Language</label>
                <select name="language" id="language" class="form-select form-select-sm select2">
                    @php $lang = old('language') ?? ($ed->language ?? ($employee->language ?? 'en')); @endphp
                    <option value="en" data-flag="https://flagcdn.com/w20/gb.png" {{ $lang === 'en' ? 'selected' : '' }}>English</option>
                    <option value="bn" data-flag="https://flagcdn.com/w20/bd.png" {{ $lang === 'bn' ? 'selected' : '' }}>Bengali</option>
                    <option value="hi" data-flag="https://flagcdn.com/w20/in.png" {{ $lang === 'hi' ? 'selected' : '' }}>Hindi</option>
                    <option value="fr" data-flag="https://flagcdn.com/w20/fr.png" {{ $lang === 'fr' ? 'selected' : '' }}>French</option>
                    <option value="de" data-flag="https://flagcdn.com/w20/de.png" {{ $lang === 'de' ? 'selected' : '' }}>German</option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">User Role <sup class="text-danger">*</sup></label>
                @php $role = old('user_role') ?? ($employee->role ?? 'employee') @endphp
                <select name="user_role" class="form-control select-picker" required>
                    <option value="">Select Role</option>
                    <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>Employee</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Address</label>
                <textarea name="address" class="form-control">{{ old('address') ?? ($ed->address ?? $employee->address ?? '') }}</textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">About</label>
                <textarea name="about" class="form-control">{{ old('about') ?? ($ed->about ?? $employee->about ?? '') }}</textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Login Allowed?</label>
                @php $loginAllowed = (string) (old('login_allowed') ?? (string)($ed->login_allowed ?? $employee->login_allowed ?? '1')); @endphp
                <select name="login_allowed" class="form-control">
                    <option value="1" {{ $loginAllowed === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ $loginAllowed === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Email Notifications?</label>
                @php $emailNotif = (string) (old('email_notifications') ?? (string)($ed->email_notifications ?? $employee->email_notifications ?? '1')); @endphp
                <select name="email_notifications" class="form-control">
                    <option value="1" {{ $emailNotif === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ $emailNotif === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Skills</label>
                <textarea name="skills" class="form-control">{{ old('skills') ?? ($ed->skills ?? $employee->skills ?? '') }}</textarea>
            </div>

            <div class="col-md-4 mb-3">
                <label for="employment_type" class="form-label" style="color: #4a1c6c; font-weight: 500;">Employment Type</label>
                @php $employmentType = old('employment_type') ?? ($ed->employment_type ?? $employee->employment_type ?? '') @endphp
                <select name="employment_type" id="employment_type" class="form-control select-picker" data-size="8">
                    <option value="">Select</option>
                    <option value="full_time" {{ $employmentType === 'full_time' ? 'selected' : '' }}>Full Time</option>
                    <option value="part_time" {{ $employmentType === 'part_time' ? 'selected' : '' }}>Part Time</option>
                    <option value="on_contract" {{ $employmentType === 'on_contract' ? 'selected' : '' }}>On Contract</option>
                    <option value="internship" {{ $employmentType === 'internship' ? 'selected' : '' }}>Internship</option>
                    <option value="trainee" {{ $employmentType === 'trainee' ? 'selected' : '' }}>Trainee</option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="marital_status" class="form-label" style="color: #4a1c6c; font-weight: 500;">Marital Status</label>
                @php $marital = old('marital_status') ?? ($ed->marital_status ?? $employee->marital_status ?? '') @endphp
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

            <div class="col-md-4 mb-3">
                <label style="color: #4a1c6c; font-weight: 500;">Business Address <sup class="text-danger">*</sup></label>
                <textarea name="business_address" class="form-control" required>{{ old('business_address') ?? ($ed->business_address ?? $employee->business_address ?? 'Kolkata') }}</textarea>
            </div>

            <div class="col-md-4 mb-3 d-flex align-items-center">
                <label class="me-3 mb-0" style="min-width: 70px; color: #4a1c6c; font-weight: 500;">Status <sup class="text-danger">*</sup></label>
                @php $status = old('status') ?? ($ed->status ?? 'Active') @endphp
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
                <label style="color: #4a1c6c; font-weight: 500;">Exit Date</label>
                <input type="date" name="exit_date" id="exit_date" class="form-control" value="{{ old('exit_date') ?? fmtDate($ed->exit_date ?? '') }}">
            </div>
        </div>

        <div class="text-start mt-3">
            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #8a2be2 0%, #6a1b9a 100%); border: none; padding: 10px 25px; font-weight: 500; box-shadow: 0 2px 5px rgba(106, 27, 154, 0.3);">Update</button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary" style="background-color: #f0f0f0; color: #4a4a4a; border: 1px solid #d0d0d0; padding: 10px 25px;">Cancel</a>
        </div>
    </form>

    {{-- Modals: Parent Department, Department, Designation --}}
    <div class="modal fade" id="prtModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="addPrtDptForm">@csrf
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #f5e6ff 0%, #e6ccff 100%);">
                        <h5 class="modal-title" style="color: #4a1c6c;">Manage Parent Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered mb-4">
                            <thead class="table-light">
                                <tr><th>#</th><th>Parent Department Name</th><th width="120">Action</th></tr>
                            </thead>
                            <tbody id="prt-dpt-list">
                                @foreach($prtdepartments as $index => $prt)
                                    <tr id="prt-row-{{ $prt->id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $prt->dpt_name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger delete-prt" data-id="{{ $prt->id }}">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mb-3">
                            <label style="color: #4a1c6c; font-weight: 500;">Parent Department Name <sup class="text-danger">*</sup></label>
                            <input type="text" name="dpt_name" id="prt_dpt_name" class="form-control" required>
                            <div id="prt-group-error" class="text-danger d-none mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #8a2be2 0%, #6a1b9a 100%); border: none;">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="dptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="addDptForm">@csrf
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #f5e6ff 0%, #e6ccff 100%);">
                        <h5 class="modal-title" style="color: #4a1c6c;">Manage Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered mb-4">
                            <thead class="table-light">
                                <tr><th>#</th><th>Parent Department Name</th><th>Department Name</th><th width="120">Action</th></tr>
                            </thead>
                            <tbody id="dpt-list">
                                @foreach($departments as $index => $dpt)
                                    <tr id="dpt-row-{{ $dpt->id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $dpt->parent?->dpt_name ?? 'N/A' }}</td>
                                        <td>{{ $dpt->dpt_name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger delete-dpt" data-id="{{ $dpt->id }}">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mb-3">
                            <label style="color: #4a1c6c; font-weight: 500;">Parent Department (optional)</label>
                            <select name="parent_dpt_id" id="dpt_parent_select" class="form-control">
                                <option value="">None</option>
                                @foreach($prtdepartments as $pd)
                                    <option value="{{ $pd->id }}">{{ $pd->dpt_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label style="color: #4a1c6c; font-weight: 500;">Department Name <sup class="text-danger">*</sup></label>
                            <input type="text" name="dpt_name" id="dpt_name" class="form-control" required>
                            <div id="dpt-group-error" class="text-danger d-none mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #8a2be2 0%, #6a1b9a 100%); border: none;">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="designationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addDesignationForm">@csrf
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #f5e6ff 0%, #e6ccff 100%);">
                        <h5 class="modal-title" style="color: #4a1c6c;">Manage Designations</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($designations->isNotEmpty())
                            <div class="mb-3">
                                <label class="form-label" style="color: #4a1c6c; font-weight: 500;">Existing Designations</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-3">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Level</th>
                                                <th width="100">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="designation-list">
                                            @foreach($designations as $des)
                                                <tr id="des-row-{{ $des->id }}">
                                                    <td>{{ $des->name }}</td>
                                                    <td>{{ $des->level ?? 'Not Set' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning edit-designation"
                                                                data-id="{{ $des->id }}"
                                                                data-name="{{ $des->name }}"
                                                                data-level="{{ $des->level ?? '' }}"
                                                                title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger delete-designation" data-id="{{ $des->id }}" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="border-top pt-3">
                            <h6 style="color: #4a1c6c; font-weight: 600;">Add New Designation</h6>
                            <div class="mb-3">
                                <label style="color: #4a1c6c; font-weight: 500;">Designation Name <sup class="text-danger">*</sup></label>
                                <input type="text" name="name" id="designationName" class="form-control" required>
                                <div class="text-danger mt-2 d-none" id="designation-error"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" style="color: #4a1c6c; font-weight: 500;">Designation Level <span class="text-danger">*</span></label>
                                <input type="number" min="0" max="6" name="level" class="form-control" id="designationLevel" placeholder="Enter level (0-6)" required>
                                <small class="text-muted">Level range: 0-6 (e.g., 0=Intern, 1=Associate, 2=Sr. Associate, etc.)</small>
                            </div>
                        </div>

                        <!-- Hidden fields for edit mode -->
                        <input type="hidden" id="edit_designation_id" value="">
                        <input type="hidden" name="status" value="Active">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveDesignationBtn" style="background: linear-gradient(135deg, #8a2be2 0%, #6a1b9a 100%); border: none;">Add Designation</button>
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
input[disabled],
input[readonly] {
  background-color: #fff !important;
  opacity: 1 !important;
  color: #212529 !important;
}
input.readonly-like-normal {
  border-color: #d1d5db;
  box-shadow: none;
}
input.readonly-locked {
  pointer-events: none;
  caret-color: transparent;
}
input.readonly-locked:focus {
  outline: none;
  box-shadow: none;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.form-control:focus, .btn:focus, .select2:focus {
    border-color: #b380ff !important;
    box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.15) !important;
}
</style>
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const $el = id => document.getElementById(id);

function toggleExitDate() {
    const isInactive = $el('status-inactive') && $el('status-inactive').checked;
    const container = $el('exit-date-container');
    if (container) container.style.display = isInactive ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    toggleExitDate();

    const empAuto = $el('emp_auto');
    const empCustom = $el('emp_custom');
    const empInput = $el('employee_id_input');

    function updateEmpInputState() {
        if (!empInput) return;
        empInput.readOnly = true;
        empInput.required = false;
        empInput.classList.add('readonly-like-normal');
    }

    if (empAuto) empAuto.addEventListener('change', updateEmpInputState);
    if (empCustom) empCustom.addEventListener('change', updateEmpInputState);
    updateEmpInputState();

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

    document.querySelectorAll('.generate-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const passwordField = $el('password');
            if (!passwordField) return;
            const randomPassword = Math.random().toString(36).slice(-10) + '!A1';
            passwordField.value = randomPassword;
        });
    });

    const desBtn = $el('openDesignationModalBtn');
    if (desBtn) desBtn.addEventListener('click', function () {
        // Reset form to add mode
        $('#addDesignationForm')[0].reset();
        $('#edit_designation_id').val('');
        $('#saveDesignationBtn').text('Add Designation');
        // $('.modal-title').text('');
        $('#designation-error').addClass('d-none').text('');

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

    const mobileOnly = $el('mobile_only_digits');
    if (mobileOnly) {
        mobileOnly.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 10);
            if (v.length > 0 && v[0] === '0') v = v.replace(/^0+/, '');
            this.value = v;
        });
    }

    const form = document.getElementById('employeeForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const dobEl = $el('dob');
            if (dobEl && !dobEl.value) {
                e.preventDefault();
                dobEl.focus();
                alert('Please provide Date of Birth (DOB). It is required.');
                return false;
            }

            const probation = $el('probation_end_date') ? $el('probation_end_date').value : '';
            const noticeStart = $el('notice_start_date') ? $el('notice_start_date').value : '';
            const noticeEnd = $el('notice_end_date') ? $el('notice_end_date').value : '';

            if ((noticeStart && !noticeEnd) || (!noticeStart && noticeEnd)) {
                e.preventDefault();
                alert('If you set a notice period, please provide both Notice Start Date and Notice End Date.');
                return false;
            }

            if (noticeStart && noticeEnd) {
                if (new Date(noticeStart) > new Date(noticeEnd)) {
                    e.preventDefault();
                    alert('Notice Period Start Date cannot be after Notice Period End Date.');
                    return false;
                }
            }

            if (probation && (noticeStart || noticeEnd)) {
                if (!confirm('Both probation and notice period dates are filled. The form will prioritize probation and clear notice dates on save. Continue?')) {
                    e.preventDefault();
                    return false;
                }
            }

            const mobileEl = $el('mobile_only_digits');
            if (mobileEl) {
                const m = mobileEl.value.trim();
                if (!/^[1-9]\d{9}$/.test(m)) {
                    e.preventDefault();
                    mobileEl.focus();
                    alert('Please enter a valid 10-digit mobile number (no leading 0).');
                    return false;
                }
                const hidden = $el('mobile_with_code');
                if (hidden) hidden.value = '+91' + m;
            }
        });
    }
});
</script>

<script>
$(document).ready(function() {
    // Load sub-departments when parent department changes
    function loadSubDepartments(parentId, selectedId = null) {
        const $sub = $('#department_id');
        $sub.empty().append('<option value="">Select</option>');
        if (!parentId) return;

        let url = '{{ route("employees.sub-departments", ":id") }}';
        url = url.replace(':id', parentId);

        $.get(url)
         .done(function (data) {
            if (!Array.isArray(data)) return;
            data.forEach(function (dept) {
                const isSelected = selectedId && parseInt(selectedId) === parseInt(dept.id);
                let text = dept.dpt_name;
                if (dept.dpt_code) text += ' (' + dept.dpt_code + ')';
                $sub.append($('<option>', { value: dept.id, text: text, selected: isSelected }));
            });
         })
         .fail(function () { console.error('Failed to load sub-departments'); });
    }

    const initialParent = $('#prt_department_id').val();
    const selectedSub   = $('#department_id').data('selected');

    if (initialParent) loadSubDepartments(initialParent, selectedSub);

    $('#prt_department_id').on('change', function () {
        loadSubDepartments($(this).val(), null);
    });

    // Handle parent department form submission
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
                    $('#prt-dpt-list').append(`<tr id="prt-row-${res.dpt.id}"><td>#</td><td>${res.dpt.dpt_name}</td><td><button type="button" class="btn btn-sm btn-danger delete-prt" data-id="${res.dpt.id}">Delete</button></td></tr>`);
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

    // Handle department form submission
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
                    $('#dpt-list').append(`<tr id="dpt-row-${res.dpt.id}"><td>#</td><td>${res.dpt.parent_name ? res.dpt.parent_name : 'N/A'}</td><td>${res.dpt.dpt_name}</td><td><button type="button" class="btn btn-sm btn-danger delete-dpt" data-id="${res.dpt.id}">Delete</button></td></tr>`);
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

    // Edit designation button click
    $(document).on('click', '.edit-designation', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const level = $(this).data('level') || '';

        $('#designationName').val(name);
        $('#designationLevel').val(level);
        $('#edit_designation_id').val(id);
        $('#saveDesignationBtn').text('Update Designation');
        $('.modal-title').text('Edit Designation');
        $('#designation-error').addClass('d-none').text('');
    });

    // Delete designation
    $(document).on('click', '.delete-designation', function () {
        const id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this designation? This may affect existing employees.')) return;

        $.ajax({
            url: `{{ url('designations') }}/${id}`,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.status === 'success') {
                    // Remove from table
                    $(`#des-row-${id}`).remove();
                    // Remove from select dropdown
                    $(`#designation_id option[value="${id}"]`).remove();

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: 'Designation deleted successfully',
                        timer: 1400,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Failed to delete designation'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete designation. Please try again.'
                });
            }
        });
    });

    // Handle designation form submission (Add/Edit)
    $('#addDesignationForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#saveDesignationBtn');
        const name = $('#designationName').val().trim();
        const level = $('#designationLevel').val();
        const editId = $('#edit_designation_id').val();
        const token = '{{ csrf_token() }}';
        const isEditMode = editId !== '';

        $('#designation-error').addClass('d-none').text('');

        // Basic validation - same as create page
        if (!name) {
            $('#designation-error').removeClass('d-none').text('Please enter a designation name.');
            return;
        }

        // FIXED: Simplified validation - same as create page
        if (level === '' || level === null) {
            $('#designation-error').removeClass('d-none').text('Please enter a level between 0-6.');
            return;
        }

        // Convert to number and validate range - ALLOW 0
        const levelNum = parseInt(level);
        if (isNaN(levelNum) || levelNum < 0 || levelNum > 6) {
            $('#designation-error').removeClass('d-none').text('Level must be a whole number between 0-6.');
            return;
        }

        $btn.prop('disabled', true);

        let url, method, data;

        if (isEditMode) {
            // Update existing designation
            url = `{{ url('designations') }}/${editId}`;
            method = 'POST';
            data = {
                _token: token,
                _method: 'PUT',
                name: name,
                level: levelNum,
                status: 'Active'
            };
        } else {
            // Create new designation
            url = '{{ route('designations.ajax.store') }}';
            method = 'POST';
            data = {
                _token: token,
                name: name,
                level: levelNum,
                status: 'Active'
            };
        }

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function (res) {
                if (res.designation && res.designation.id) {
                    let label = res.designation.name;
                    if (res.designation.unique_code) {
                        label += ` (${res.designation.unique_code})`;
                    }
                    if (res.designation.level !== null) {
                        label += ` - Level ${res.designation.level}`;
                    }

                    if (isEditMode) {
                        // Update in table
                        $(`#des-row-${res.designation.id} td:first`).text(res.designation.name);
                        $(`#des-row-${res.designation.id} td:nth-child(2)`).text(res.designation.level !== null ? res.designation.level : 'Not Set');

                        // Update edit button data attributes
                        $(`#des-row-${res.designation.id} .edit-designation`)
                            .data('name', res.designation.name)
                            .data('level', res.designation.level !== null ? res.designation.level : '');

                        // Update in select dropdown
                        const $option = $(`#designation_id option[value="${res.designation.id}"]`);
                        if ($option.length) {
                            $option.text(label);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: 'Designation updated successfully',
                            timer: 1400,
                            showConfirmButton: false
                        });
                    } else {
                        // Add to table
                        const newRow = `
                            <tr id="des-row-${res.designation.id}">
                                <td>${res.designation.name}</td>
                                <td>${res.designation.level !== null ? res.designation.level : 'Not Set'}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning edit-designation"
                                            data-id="${res.designation.id}"
                                            data-name="${res.designation.name}"
                                            data-level="${res.designation.level !== null ? res.designation.level : ''}"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-designation" data-id="${res.designation.id}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        $('#designation-list').append(newRow);

                        // Add to select dropdown
                        $('#designation_id').append(`<option value="${res.designation.id}">${label}</option>`);

                        Swal.fire({
                            icon: 'success',
                            title: 'Created',
                            text: 'Designation created successfully',
                            timer: 1400,
                            showConfirmButton: false
                        });
                    }

                    // Reset form
                    $('#addDesignationForm')[0].reset();
                    $('#edit_designation_id').val('');
                    $('#saveDesignationBtn').text('Add Designation');
                    $('.modal-title').text('Manage Designations');

                    // Don't close modal on edit, allow more operations
                    if (!isEditMode) {
                        $('#designationModal').modal('hide');
                    }
                } else {
                    $('#designation-error').removeClass('d-none').text(res.message || 'Operation completed but unexpected response.');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                    if (msg.toLowerCase().includes('duplicate') || msg.toLowerCase().includes('already exists') || msg.toLowerCase().includes('taken')) {
                        msg = 'This designation name already exists. Please choose a different name.';
                    }
                } else if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                $('#designation-error').removeClass('d-none').text(msg);
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    // Initialize Select2 for country
    function formatCountry (state) {
        if (!state.id) return state.text;
        let flag = $(state.element).data("flag");
        if (flag) {
            return $('<span><img src="' + flag + '" width="20" class="me-2"/> ' + state.text + '</span>');
        }
        return state.text;
    }

    if ($('#country').length) {
        $('#country').select2({
            theme: "bootstrap-5",
            templateResult: formatCountry,
            templateSelection: formatCountry,
            placeholder: "Select Country",
            allowClear: true
        });

        const $country = $('#country');
        const indiaOption = $country.find('option').filter(function(){ return $(this).text().trim() === 'India'; }).first();
        if (indiaOption.length) {
            $country.prepend(indiaOption);
            if (!$country.val()) $country.val(indiaOption.val()).trigger('change.select2');
        }
    }

    // Initialize Select2 for language if exists
    if ($('#language').length) {
        $('#language').select2({
            theme: "bootstrap-5",
            templateResult: formatCountry,
            templateSelection: formatCountry,
            placeholder: "Select Language",
            allowClear: true
        });
    }
});
</script>

<script>
try {
  if (typeof flatpickr !== 'undefined') {
    const commonOpts = { dateFormat: "Y-m-d", allowInput: true, altInput: true, altFormat: "d-m-Y" };
    if (document.getElementById('dob')) flatpickr("#dob", commonOpts);
    if (document.getElementById('joining_date')) flatpickr("#joining_date", commonOpts);
    if (document.getElementById('exit_date')) flatpickr("#exit_date", { dateFormat: "Y-m-d" });
  }
} catch (e) { console.warn('flatpickr init error', e); }
</script>
@endpush
