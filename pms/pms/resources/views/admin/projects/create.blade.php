@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
    <div class="container">
        <br>
        <h5>Create Project</h5>
        
         @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

       <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-3">
        
        <!-- SHORTCODE: Auto or Manual choice -->
        <div class="col-md-6">
            <label>Short Code <sup class="text-danger">*</sup></label>

            <div class="mb-2 d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="shortcode_option_radio" id="shortcode_auto" value="auto" checked>
                    <label class="form-check-label" for="shortcode_auto">Auto-generate</label>
                </div>

                <!--<div class="form-check">-->
                <!--    <input class="form-check-input" type="radio" name="shortcode_option_radio" id="shortcode_manual_opt" value="manual">-->
                <!--    <label class="form-check-label" for="shortcode_manual_opt">Enter manually</label>-->
                <!--</div>-->
            </div>

            <!-- Hidden field that will ALWAYS be submitted (sync'd by JS) -->
            <input type="hidden" name="shortcode_option" id="shortcode_option" value="auto">

            <!-- Informational display for auto -->
           <input type="text"
       id="shortcode_display"
       class="form-control mb-2"
       value="{{ $nextProjectCode ?? 'Will be generated automatically' }}"
       readonly>


            <!-- Manual input (hidden by default) -->
            <input type="text" name="shortcode_manual" id="shortcode_manual" class="form-control d-none" placeholder="Enter shortcode e.g. Xink25-26/0001" value="{{ old('shortcode_manual') }}">

            <!--<small class="form-text text-muted">Choose Auto to have the system generate the next code, or Manual to enter your own unique code.</small>-->

            @error('shortcode_manual')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label>Project Name <sup class="text-danger">*</sup></label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>
        
        
        <div class="col-md-4">
            <label>Start Date <sup class="text-danger">*</sup></label>
            <input type="date" name="start_date" class="form-control" required value="{{ old('start_date') }}">
        </div>

        <div class="col-md-4">
            {{-- CHANGED: give the star an id so we can hide/show it --}}
            <label>Deadline <sup class="text-danger" id="deadline_required">*</sup></label>
            <input type="date" name="deadline" class="form-control" id="deadline_input" value="{{ old('deadline') }}">
        </div>
        
        <div class="col-md-4 d-flex align-items-center pt-4">
            <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="without_deadline" id="without_deadline" {{ old('without_deadline') ? 'checked' : '' }}>
                    
                   
                    <label class="form-check-label" for="without_deadline">
                        No deadline for this project
                    </label>
                </div>

        </div>
        
        <div class="col-md-4">
            <label>Project Category <sup class="text-danger">*</sup></label>
            <div class="input-group">
                <select name="category_id" id="project_category_id" class="form-control" required>
                    <option value="">Select</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#catModal">
                    Add
                </button>
            </div>
        </div>
        


        
        <div class="col-md-4">
    <label>Parent Department <sup class="text-danger">*</sup></label>
    <select name="parent_dpt_id" id="parent_dpt_id" class="form-control" required>
        <option value="">Select</option>
        @foreach($prtdepartments as $prt)
            <option value="{{ $prt->id }}">{{ $prt->dpt_name }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label>Department <sup class="text-danger"></sup></label>
    <select name="department_id" id="department_id" class="form-control" >
        <option value="">Select parent department first</option>
    </select>
</div>

        
       <div class="col-md-4">
        {{-- CHANGED: make client mandatory visually --}}
        <label>Client <sup class="text-danger">*</sup></label>
        <div class="input-group">
            {{-- CHANGED: add required attribute --}}
            <select name="client_id" id="client_id" class="form-control" required>
                <option value="">Select</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#clientModal">
                Add
            </button>
        </div>
    </div>


        <div class="col-md-6">
            <label>Project Summary</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>


      
        
        <div class="col-md-6">
            <label>Notes</label>
            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
        </div>
        
        
        <div class="row">

    <!-- Public Gantt Chart -->
    <div class="col-md-12 col-lg-4">
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-12 w-100 mt-3">Public Gantt Chart</label>
            <div class="d-flex">
                <div class="form-check-inline custom-control custom-radio mt-2 mr-3">
                    <input type="radio" value="enable" class="custom-control-input" 
                           id="public_gantt_chart-yes" name="public_gantt_chart" 
                           {{ old('public_gantt_chart', 'enable') == 'enable' ? 'checked' : '' }}>
                    <label class="custom-control-label pt-1 cursor-pointer" for="public_gantt_chart-yes">Enable</label>
                </div>
                <div class="form-check-inline custom-control custom-radio mt-2 mr-3">
                    <input type="radio" value="disable" class="custom-control-input" 
                           id="public_gantt_chart-no" name="public_gantt_chart" 
                           {{ old('public_gantt_chart') == 'disable' ? 'checked' : '' }}>
                    <label class="custom-control-label pt-1 cursor-pointer" for="public_gantt_chart-no">Disable</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Public Task Board -->
    <div class="col-md-12 col-lg-4">
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-12 w-100 mt-3">Public Task Board</label>
            <div class="d-flex">
                <div class="form-check-inline custom-control custom-radio mt-2 mr-3">
                    <input type="radio" value="enable" class="custom-control-input" 
                           id="public_taskboard-yes" name="public_taskboard"
                           {{ old('public_taskboard', 'enable') == 'enable' ? 'checked' : '' }}>
                    <label class="custom-control-label pt-1 cursor-pointer" for="public_taskboard-yes">Enable</label>
                </div>
                <div class="form-check-inline custom-control custom-radio mt-2 mr-3">
                    <input type="radio" value="disable" class="custom-control-input" 
                           id="public_taskboard-no" name="public_taskboard"
                           {{ old('public_taskboard') == 'disable' ? 'checked' : '' }}>
                    <label class="custom-control-label pt-1 cursor-pointer" for="public_taskboard-no">Disable</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Approval Required -->
    <div class="col-md-12 col-lg-4">
        <div class="form-group my-3">
            <label class="f-14 text-dark-grey mb-12 w-100 mt-3">Task needs approval by Admin/Project Admin</label>
            <div class="d-flex">
                <div class="form-check-inline custom-control custom-radio mt-2 mr-3">
                    <input type="radio" value="1" class="custom-control-input" 
                           id="need_approval_by_admin-yes" name="need_approval_by_admin"
                           {{ old('need_approval_by_admin') == '1' ? 'checked' : '' }}>
                    <label class="custom-control-label pt-1 cursor-pointer" for="need_approval_by_admin-yes">Enable</label>
                </div>
                <div class="form-check-inline custom-control custom-radio mt-2 mr-3">
                    <input type="radio" value="0" class="custom-control-input" 
                           id="need_approval_by_admin-no" name="need_approval_by_admin"
                           {{ old('need_approval_by_admin', '0') == '0' ? 'checked' : '' }}>
                    <label class="custom-control-label pt-1 cursor-pointer" for="need_approval_by_admin-no">Disable</label>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="col-sm-12">
    <div class="form-group">
        <div class="mt-2 d-flex">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="public" id="is_public" autocomplete="off" {{ old('public') ? 'checked' : '' }}>
                <label class="form-check-label form_custom_label text-dark-grey pl-2 mr-4 justify-content-start cursor-pointer checkmark-20 pt-1 text-wrap text-break" for="is_public">
                    Create Public Project
                </label>
            </div>
        </div>
    </div>
</div>

        
<div class="col-md-12">
    <label>Add Project Members <sup class="text-danger">*</sup></label>
    <div class="row">
        <div class="col-md-9">
            <select name="employee_ids[]" id="projectMembers" class="form-control" multiple>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" data-fullname="{{ $u->name }}">
                        {{ $u->name }} ({{ $u->employee_id }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-start">
            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#employeeModal">
                + Add Employee
            </button>
        </div>
    </div>
</div>




        
        <!-- <div class="col-md-6">-->
        <!--    <label>Status<sup class="text-danger">*</sup></label>-->
        <!--    <select name="status" class="form-control" required>-->
        <!--        <option value="not started">Not Started</option>-->
        <!--        <option value="in progress">In Progress</option>-->
        <!--        <option value="on hold">On Hold</option>-->
        <!--        <option value="completed">Completed</option>-->
        <!--    </select>-->
        <!--</div>-->
        
        
        <!-- Other Details Section -->
        <div class="col-12 mt-4">
            <h5 class="cursor-pointer" data-bs-toggle="collapse" data-bs-target="#otherDetails" aria-expanded="false" aria-controls="otherDetails">
                Other Details <i class="bi bi-chevron-down"></i>
            </h5>
        
            <div class="collapse" id="otherDetails">
                <div class="card card-body mt-3">
        
                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="project_file" class="form-label">Add File</label>
                        <input type="file" class="form-control" id="project_file" name="project_file">
                    </div>
        
                   <div class="row">
                    <!-- Currency -->
                    <div class="col-md-4 mb-3">
                        <label for="currency" class="form-label">Currency</label>
                        <select id="currency" name="currency_id" class="form-select select2">
                            <option value="">Select</option>
                            @foreach($currency as $c)
                                <option value="{{ $c->id }}" {{ old('currency_id') == $c->id ? 'selected' : '' }}>{{ $c->currency_name }}</option>
                            @endforeach
                        </select>
                    </div>

                
                    <!-- Project Budget -->
                    <div class="col-md-4 mb-3">
                        <label for="project_budget" class="form-label">Project Budget</label>
                        <input type="number" class="form-control" id="project_budget" name="project_budget" placeholder="e.g. 10000" value="{{ old('project_budget') }}">
                    </div>
                
                    <!-- Hours Estimate -->
                    <div class="col-md-4 mb-3">
                        <label for="hours_estimate" class="form-label">Hours Estimate (In Hours)</label>
                        <input type="number" class="form-control" id="hours_allocated" name="hours_allocated" placeholder="e.g. 50" value="{{ old('hours_allocated') }}">
                    </div>
                </div>

        
                    <!-- Checkboxes -->
                    <div class="row">
                        <!-- Manual Timelog -->
                        <div class="col-md-6 col-lg-3">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="manual_timelog" id="manual_timelog" {{ old('manual_timelog') ? 'checked' : '' }}>
                                <label class="form-check-label" for="manual_timelog">
                                    Allow manual time logs
                                </label>
                            </div>
                        </div>
        
                        <!-- Miroboard -->
                        <div class="col-md-6 col-lg-3">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="enable_miroboard" id="miroboard_checkbox" {{ old('enable_miroboard') ? 'checked' : '' }}>
                                <label class="form-check-label" for="miroboard_checkbox">
                                    Enable Miroboard
                                </label>
                            </div>
                        </div>
        
                        <!-- Client Task Notification -->
                        <div class="col-md-6 col-lg-4">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="allow_client_notification" id="client_task_notification" {{ old('allow_client_notification') ? 'checked' : '' }}>
                                <label class="form-check-label" for="client_task_notification">
                                    Send task notification to client
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12">
            <button type="submit" class="btn btn-primary">Save</button>
            
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>


<!-- category Modal -->
<div class="modal fade" id="catModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header with Close -->
      <div class="modal-header">
        <h5 class="modal-title">Project Categories</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Group List Table -->
        <table class="table table-bordered mb-4">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Category Name</th>
              <th width="120">Action</th>
            </tr>
          </thead>
          <tbody id="dpt-list">
            @foreach($categories as $index => $dpt)
              <tr id="cat-row-{{ $dpt->id }}">
                <td>{{ $index + 1 }}</td>
                <td>{{ $dpt->category_name }}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger delete-cat" data-id="{{ $dpt->id }}">
                        Delete
                    </button>
                </td>
            </tr>

            @endforeach
          </tbody>
        </table>

        <!-- Add New Category Form -->
        <form id="addCatForm">
          @csrf
          <div id="cat-error" class="alert alert-danger d-none"></div>
          <div class="mb-3">
            <label>Category Name</label>
            <input type="text" name="category_name" class="form-control" required>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<!--clientModal-->

<!-- ✅ Client Modal -->
<div class="modal fade" id="clientModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="addClientForm">
          @csrf
          <div id="client-error" class="alert alert-danger d-none"></div>

          <div class="mb-3">
            <label>Client Name <sup class="text-danger">*</sup></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
          </div>

          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. johndoe@example.com">
          </div>

          <div class="mb-3">
            <label>Company Name</label>
            <input type="text" name="company_name" class="form-control" placeholder="e.g. Acme Corporation">
          </div>

          <div class="mb-3">
            <label>Login Allowed?</label>
            <div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="login_allowed" value="1" checked>
                <label class="form-check-label">Yes</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="login_allowed" value="0">
                <label class="form-check-label">No</label>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- Employee Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                 <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data" id="employeeForm">
        @csrf

        <h5 class="mb-3">Account Details</h5>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Employee ID <sup class="text-danger">*</sup></label>
                <input type="text" name="employee_id" class="form-control" placeholder="Employee ID is the unique ID distributed to employees" required>
                @error('employee_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label>Salutation</label>
                <select name="salutation" class="form-control">
                    <option value="">Select</option>
                    <option value="Mr">Mr</option>
                    <option value="Mrs">Mrs</option>
                    <option value="Miss">Miss</option>
                    <option value="Dr">Dr</option>
                    <option value="Sir">Sir</option>
                    <option value="Madam">Madam</option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label>Employee Name <sup class="text-danger">*</sup></label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label>Email <sup class="text-danger">*</sup></label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
           <div class="col-md-4 mb-3">
            <label class="form-label" for="password">Password <sup class="text-danger">*</sup></label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" autocomplete="off" minlength="9">
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                
                <button type="button" class="btn btn-outline-secondary toggle-password" title="Show/Hide Password">
                    <i class="fa fa-eye"></i>
                </button>
        
                <button type="button" class="btn btn-outline-secondary generate-password" title="Generate Random Password">
                    <i class="fa fa-random"></i>
                </button>
            </div>
            <small class="form-text text-muted">Must have at least 9 characters</small>
        </div>

            <div class="col-md-4 mb-3">
                <label>Designation <sup class="text-danger">*</sup></label>
                <select name="designation_id" class="form-control" required>
                    <option value="">Select</option>
                    @foreach($designations as $designation)
                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-4 mb-3">
                <label>Parent Department <sup class="text-danger">*</sup></label>
                 <div class="input-group">
                    <select name="parent_dpt_id" id="prt_department_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($prtdepartments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->dpt_name }}</option>
                        @endforeach
                    </select>
                    
                     <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#prtModal">
                                Add
                    </button>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label>Department <sup class="text-danger">*</sup></label>
                <div class="input-group">
                    <select name="department_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->dpt_name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#dptModal">
                                Add
                    </button>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control">
            </div>

           <div class="col-md-4 mb-3">
            <label class="form-label fw-semibold">Country <sup class="text-danger">*</sup></label>
        
            <!-- Country dropdown -->
            <select name="country" id="country" class="form-select form-select-sm select2">
                <option value="">Select Country</option>
                @foreach($countries as $country)
                    <option value="{{ $country->name }}" 
                            data-flag="{{ $country->flag_url }}"> <!-- keep flag url in DB -->
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
        </div>

            <div class="col-md-4 mb-3">
                <label>Mobile <sup class="text-danger">*</sup></label>
                <input type="text" name="mobile" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label>Gender</label>
                <select name="gender" class="form-control">
                    <option value="">Select</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
            </div>

            
            @php
                $today = \Carbon\Carbon::now()->format('d-m-Y');
            @endphp
            
            <div class="col-md-4 mb-3">
                
                    <label>Joining Date <sup class="text-danger">*</sup></label>
            
                    <input type="date"  required
                           class="form-control date-picker height-35 f-14" 
                           placeholder="Select Date" 
                           name="joining_date" 
                           id="joining_date" 
                           autocomplete="off"
                            value="{{ date('Y-m-d') }}"  max="{{ date('Y-m-d') }}">
              
            </div>


            <div class="col-md-4 mb-3">
                <label>Date of Birth</label>
                <input type="date" name="dob" id="dob" class="form-control" max="{{ date('Y-m-d') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label>Reporting To</label>
                <select name="reporting_to" class="form-control">
                    <option value="">Select</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

           
            
            <div class="col-md-4 mb-3">
            <label class="form-label fw-semibold">Change Language</label>
            <select name="language" id="language" class="form-select form-select-sm select2">
                <option value="en" data-flag="https://flagcdn.com/w20/gb.png">English</option>
                <option value="bn" data-flag="https://flagcdn.com/w20/bd.png">Bengali</option>
                <option value="hi" data-flag="https://flagcdn.com/w20/in.png">Hindi</option>
                <option value="fr" data-flag="https://flagcdn.com/w20/fr.png">French</option>
                <option value="de" data-flag="https://flagcdn.com/w20/de.png">German</option>
            </select>
        </div>

            <div class="col-md-4 mb-3">
                <label>User Role <sup class="text-danger">*</sup></label>
                <select name="user_role" class="form-control select-picker" required>
                    <option value="">Select Role</option>
                    <option value="employee">Employee</option>
                    <option value="admin">Admin</option>
                </select>

            </div>

            <div class="col-md-6 mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control"></textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label>About</label>
                <textarea name="about" class="form-control"></textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label>Login Allowed?</label>
                <select name="login_allowed" class="form-control">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Email Notifications?</label>
                <select name="email_notifications" class="form-control">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label>Hourly Rate</label>
                <input type="number" step="0.01" name="hourly_rate" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Slack Member ID</label>
                <input type="text" name="slack_member_id" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Skills</label>
                <textarea name="skills" class="form-control"></textarea>
            </div>

            <div class="col-md-4 mb-3">
                <label>Probation End Date</label>
                <input type="date" name="probation_end_date" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Notice Period Start Date</label>
                <input type="date" name="notice_start_date" class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label>Notice Period End Date</label>
                <input type="date" name="notice_end_date" class="form-control">
            </div>

           
            
            <div class="col-md-4 mb-3">
                <label for="employment_type" class="form-label">Employment Type</label>
                <select name="employment_type" id="employment_type" class="form-control select-picker" data-size="8">
                    <option value="">Select</option>
                    <option value="full_time">Full Time</option>
                    <option value="part_time">Part Time</option>
                    <option value="on_contract">On Contract</option>
                    <option value="internship">Internship</option>
                    <option value="trainee">Trainee</option>
                </select>
            </div>


            
            

            <div class="col-md-4 mb-3">
                <label for="marital_status" class="form-label">Marital Status</label>
                <select name="marital_status" id="marital_status" class="form-control select-picker" data-size="8">
                    <option value="">Select</option>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="widower">Widower</option>
                    <option value="widow">Widow</option>
                    <option value="separate">Separate</option>
                    <option value="divorced">Divorced</option>
                    <option value="engaged">Engaged</option>
                </select>
            </div>


            <div class="col-md-4 mb-3">
                <label>Business Address <sup class="text-danger">*</sup></label>
                <textarea name="business_address" class="form-control" required>Kolkata</textarea>
            </div>
            
            <div class="col-md-4 mb-3 d-flex align-items-center">
                <label class="me-3 mb-0" style="min-width: 70px;">Status <sup class="text-danger">*</sup></label>
                <div class="d-flex gap-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status-active" value="Active" checked onchange="toggleExitDate()">
                        <label class="form-check-label" for="status-active">Active</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status-inactive" value="Inactive" onchange="toggleExitDate()">
                        <label class="form-check-label" for="status-inactive">Inactive</label>
                    </div>
                </div>
            </div>

        
        <div class="col-md-4" id="exit-date-container" >
            <label>Exit Date <sup class="text-danger">*</sup></label>
            <input type="date" name="exit_date" id="exit_date" class="form-control">
        </div>
        </div>
      &nbsp;
        <div class="text-start">
            <button type="submit" class="btn btn-primary">Save</button>
            
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="employeeForm" class="btn btn-primary">Save</button>
            </div>

        </div>
    </div>
</div>


 <!-- Parent Dpt Modal -->
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
                            <!-- Group List Table -->
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
        
                            <!-- Add New Group -->
                            <div class="mb-3">
                                <label>Parent Department Name <sup class="text-danger">*</sup></label>
                                <input type="text" name="dpt_name" class="form-control" required>
                                <div id="group-error" class="text-danger d-none mt-2"></div>
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

<!--dpt model-->
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
                    <!-- Group List Table -->
                    <table class="table table-bordered mb-4">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Parent Department Name</th>
                                <th>Department Name</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <!-- Modal table -->
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

                    <!-- Add New Dpt -->
                    <div class="mb-3">
                        <label>Parent Department (optional)</label>
                        <select name="parent_dpt_id" class="form-control">
                            <option value="">None</option>
                            @foreach($prtdepartments as $pd)
                                <option value="{{ $pd->id }}">{{ $pd->dpt_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label>Department Name <sup class="text-danger">*</sup></label>
                        <input type="text" name="dpt_name" class="form-control" required>
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
    </div>
</main>

@endsection

@section('js')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<!-- Bootstrap-select CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css" />
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Bootstrap-select JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Shortcode toggle + hidden fallback sync
    (function () {
        function setShortcodeOption(val) {
            const hidden = document.getElementById('shortcode_option');
            if (hidden) hidden.value = val;
        }

        function toggleShortcodeInputs() {
            const manualInput = document.getElementById('shortcode_manual');
            const display = document.getElementById('shortcode_display');
            const manualOpt = document.getElementById('shortcode_manual_opt');

            if (manualOpt && manualOpt.checked) {
                manualInput.classList.remove('d-none');
                display.classList.add('d-none');
                manualInput.focus();
            } else {
                manualInput.classList.add('d-none');
                display.classList.remove('d-none');
            }

            const selected = document.querySelector('input[name="shortcode_option_radio"]:checked');
            if (selected) setShortcodeOption(selected.value);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // init select2
            $('.select2').select2({
                placeholder: "Select Employees",
                allowClear: true,
                width: '100%'
            });

            $('#currency').select2({
                placeholder: "Select Currency",
                allowClear: true,
                width: '100%'
            });

            // bind radios
            const radios = document.querySelectorAll('input[name="shortcode_option_radio"]');
            radios.forEach(r => r.addEventListener('change', toggleShortcodeInputs));

            // init toggles
            toggleShortcodeInputs();

            // disable/enable deadline field + hide/show star
            $('#without_deadline').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#deadline_input').val('').prop('disabled', true);
                    $('#deadline_required').addClass('d-none');   // hide *
                } else {
                    $('#deadline_input').prop('disabled', false);
                    $('#deadline_required').removeClass('d-none'); // show *
                }
            }).trigger('change'); // initialize on page load

            // Toggle password visibility
            $(document).on('click', '.toggle-password', function() {
                const input = $(this).closest('.input-group').find('input[type="password"], input[type="text"]');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Generate random password
            $(document).on('click', '.generate-password', function() {
                function randPass(len = 10) {
                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
                    let out = '';
                    for (let i = 0; i < len; i++) {
                        out += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    return out;
                }
                $(this).closest('.input-group').find('input[name="password"]').val(randPass(10));
            });

            // Modal z-index fix for nested modals
            document.addEventListener("show.bs.modal", function (event) {
                const zIndex = 1050 + 10 * document.querySelectorAll('.modal.show').length;
                event.target.style.zIndex = zIndex;
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops[backdrops.length - 1].style.zIndex = zIndex - 1;
                });
            });
        });
    })();
</script>

<script>
  // Add Project Category via AJAX
$('#addCatForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: '{{ route('project-categories.store') }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.status === 'success') {
                $('#project_category_id').append(
                    `<option value="${res.cat.id}" selected>${res.cat.category_name}</option>`
                ).trigger('change');

                $('#addCatForm')[0].reset();

                const modalEl = document.getElementById('catModal');
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalInstance.hide();

                Swal.fire({
                    title: 'Success!',
                    text: 'Project Category added successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr) {
            $('#cat-error').removeClass('d-none').text(xhr.responseJSON.message || 'Error occurred.');
        }
    });
});
</script>

<script>
  // Delete Project Category via AJAX
$(document).on('click', '.delete-cat', function () {
    const id = $(this).data('id');
    if (confirm('Are you sure you want to delete this category?')) {
        $.ajax({
            url: `{{ url('project-categories') }}/${id}`,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                if (res.status === 'success') {
                    $(`#cat-row-${id}`).remove();
                    $(`#project_category_id option[value="${id}"]`).remove();
                }
            }
        });
    }
});
</script>

<script>
  // Add Client via AJAX
$('#addClientForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: '{{ route('project.clientstore') }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.status === 'success') {
                $('#client_id').append(
                    `<option value="${res.client.id}" selected>${res.client.name}</option>`
                );

                $('#addClientForm')[0].reset();

                const modalEl = document.getElementById('clientModal');
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalInstance.hide();

                Swal.fire({
                    title: 'Success!',
                    text: 'Client added successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr) {
            $('#client-error').removeClass('d-none').text(xhr.responseJSON.message || 'Error occurred.');
        }
    });
});
</script>


<script>
    $('#parent_dpt_id').on('change', function () {
        let parentId = $(this).val();

        $('#department_id').html('<option>Loading...</option>');

        $.ajax({
            url: "{{ route('get.subdepartments', '') }}/" + parentId,
            type: 'GET',
            success: function (res) {
                let html = '<option value="">Select</option>';
                res.forEach(function (dpt) {
                    html += `<option value="${dpt.id}">${dpt.dpt_name}</option>`;
                });
                $('#department_id').html(html);
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#projectMembers').select2({
            placeholder: "Select Employees",
            allowClear: true,
            width: '100%'
        });
    });
</script>



<script>
$(document).ready(function() {
    $('#currency').on('change', function() {
        // optional: handle currency change logic
    });
});
</script>



<script>
    // ensure select2 already initialized for #projectMembers
    (function(){
        const form = document.querySelector('form[action="{{ route('projects.store') }}"]');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            // remove any previous hidden name inputs (if resubmitting)
            document.querySelectorAll('input[name="employee_names[]"]').forEach(i => i.remove());

            // get selected options from select2/native select
            const sel = document.getElementById('projectMembers');
            if (!sel) return;

            // For multi-select, selectedOptions is standard; if using select2, read .val() and map
            let selected = [];
            if (typeof $(sel).select2 === 'function') {
                // select2 mode
                const vals = $(sel).val() || [];
                vals.forEach(v => {
                    const opt = sel.querySelector(`option[value="${v}"]`);
                    if (opt) selected.push(opt);
                });
            } else {
                selected = Array.from(sel.selectedOptions || []);
            }

            // For each selected option, create a hidden input employee_names[] with the option's data-fullname
            selected.forEach(opt => {
                const fullName = opt.dataset.fullname ?? opt.textContent.trim();
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'employee_names[]';
                input.value = fullName;
                form.appendChild(input);
            });
            // allow the form to submit
        });
    })();
</script>

@endsection
