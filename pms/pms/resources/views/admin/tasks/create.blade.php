@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
    <div class="container">
        @if(isset($project))
            {{-- Sub-navigation bar --}}
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item"><a class="nav-link active" href="{{ route('projects.show', $project->id) }}">Overview</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('project-members.index', $project->id)}}">Members</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('project-files.index', $project->id)}}">Files</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('milestones.index', $project->id)}}">Milestones</a></li>
                <li class="nav-item"><a class="nav-link active" href="{{ route('projects.tasks.index', $project->id)}}">Tasks</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('projects.tasks.board', $project->id) }}">Task Board</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Timesheet</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Discussion</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Notes</a></li>
            </ul>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Add Task</h5>
            </div>
            
            @if(isset($duplicateTask))
            <div class="alert alert-info">
                You are duplicating task: <strong>{{ $duplicateTask->title }}</strong>
            </div>
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

            <form method="POST" action="{{ route('tasks.store') }}" class="card-body"  enctype="multipart/form-data">
                @csrf

                <h5 class="text-primary mb-3">Task Info</h5>
                <div class="row mb-3">
                    
                     <div class="col-md-6">
                        <label>Task short code <span class="text-danger">*</span></label>

                        @php
                            // show priority: old() -> duplicateTask -> generatedTaskCode -> ''
                            $taskCodeValue = old('task_short_code', $duplicateTask->task_short_code ?? ($generatedTaskCode ?? ''));
                            // if not duplicating, consider this create view => readonly
                            $isCreateReadonly = !isset($duplicateTask) && !isset($task);
                        @endphp

                        <input type="text" name="task_short_code" class="form-control" placeholder="Enter a task short code" value="{{ $taskCodeValue }}" {{ $isCreateReadonly ? 'readonly' : '' }} required>
                    </div>
                    
                    <div class="col-md-6">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Enter a task title" value="{{ old('title', $duplicateTask->title ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                          <label>Task Category <span class="text-danger">*</span></label>
                          <div class="d-flex">
                            <select name="category_id" class="form-select me-2" required>
                              <option value="">--</option>
                              @foreach($taskCategories as $category)
                                  <option value="{{ $category->id }}"
                                    {{ old('category_id', $duplicateTask->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                              @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#taskCategoryModal">
                              Add
                            </button>
                          </div>
                        </div>

                </div>

                <div class="row mb-3">
                    @if(isset($project))
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @else
                        <div class="col-md-6">
                            <label>Project <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select" required>
                                <option value="">--</option>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-3">
                        <label>Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3" id="dueDateWrapper">
                        <label>Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        <div class="form-check mt-1">
                            <input type="checkbox" class="form-check-input" name="without_due_date" id="noDue">
                            <label class="form-check-label" for="noDue">Without Due Date</label>
                        </div>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                       
                        <label>Assigned To <span class="text-danger">*</span></label>
                         <div class="row">
                         <div class="col-md-9">
                        <select name="assigned_to[]" multiple class="form-select select2" required>

                            <option value="">Nothing selected</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                {{ in_array($user->id, old('assigned_to', $assignedUserIds ?? [])) ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>

                            @endforeach
                        </select>
                        
                        </div>
                            <div class="col-md-3 d-flex align-items-start">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#employeeModal">
                                    + Add Employee
                                </button>
                            </div>
                        </div>
                     </div>
                </div>

                <div class="mb-4">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $duplicateTask->description ?? '') }}</textarea>
                </div>

                <h5 class="text-primary mb-3">Other Details</h5>
                <div class="row mb-3">
              
                    
                    <div class="col-md-4">
                    <label>Task Labels</label>
                    <div class="d-flex">
                       <select name="task_labels[]" class="select2 form-select" multiple>
                           @foreach($labels as $label)
                                <option value="{{ $label->id }}">{{ $label->label_name }}</option>
                            @endforeach
                            
                        </select>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#taskLabelsModal">
                            Add
                        </button>
                    </div>
                </div>


                    <div class="col-md-4">
                        <label>Milestone</label>
                        <select name="milestone_id" class="form-control select-picker">
                            <option value="">--</option>
                            
                        @foreach($milestones as $milestone)
                            <option value="{{ $milestone->id }}"
                                {{ old('milestone_id', $duplicateTask->milestone_id ?? '') == $milestone->id ? 'selected' : '' }}>
                                {{ $milestone->title }}
                            </option>

                        @endforeach
                        </select>
                        
                        
                    </div>

                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="board_column_id" class="form-control select-picker">
                            <option value="1" {{ old('board_column_id', $duplicateTask->board_column_id ?? '') == 1 ? 'selected' : '' }}>Incomplete</option>
                            <option value="2" {{ old('board_column_id', $duplicateTask->board_column_id ?? '') == 2 ? 'selected' : '' }}>To Do</option>
                            <option value="3" {{ old('board_column_id', $duplicateTask->board_column_id ?? '') == 3 ? 'selected' : '' }}>Doing</option>
                            <option value="4" {{ old('board_column_id', $duplicateTask->board_column_id ?? '') == 4 ? 'selected' : '' }}>Completed</option>
                            <option value="5" {{ old('board_column_id', $duplicateTask->board_column_id ?? '') == 5 ? 'selected' : '' }}>Waiting for Approval</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Priority</label>
                        <select name="priority" class="form-control select-picker">
                            <option value="high" {{ old('priority', $duplicateTask->priority ?? 'medium') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ old('priority', $duplicateTask->priority ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ old('priority', $duplicateTask->priority ?? 'medium') == 'low' ? 'selected' : '' }}>Low</option>

                        </select>
                    </div>
                </div>

               <div class="row">

                <div class="col-md-6 col-lg-3"> 
                    <div class="form-group d-flex mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_private" id="is_private">
                            <label class="form-check-label" for="is_private">
                                Make Private
                                <i class="fas fa-question-circle" data-toggle="popover" data-content="Private tasks are only visible to admin, assignor, and assignee."></i>
                            </label>
                        </div>
                    </div>
                </div>
            
                <div class="col-md-6 col-lg-3">
                    <div class="form-group d-flex mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="billable" id="billable" {{ old('billable', $duplicateTask->billable ?? false) ? 'checked' : '' }}>

                            <label class="form-check-label" for="billable">
                                Billable
                                <i class="fas fa-question-circle" data-toggle="popover" data-content="Invoice can be generated for this task's time log."></i>
                            </label>
                        </div>
                    </div>
                </div>
            
                <div class="col-lg-6 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="set_time_estimate" id="set_time_estimate" data-bs-toggle="collapse" data-bs-target="#timeEstimateSection">
                        <label class="form-check-label" for="set_time_estimate">Time Estimate</label>
                    </div>
                    <div class="collapse" id="timeEstimateSection">
                        <div class="row mt-2">
                            <div class="col-md-6 col-lg-3">
                                <input type="number" min="0" name="estimate_hours" class="form-control"
                                   value="{{ old('estimate_hours', $duplicateTask->estimate_hours ?? 0) }}" placeholder="Hours">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <input type="number" min="0" name="estimate_minutes" class="form-control"
                                    value="{{ old('estimate_minutes', $duplicateTask->estimate_minutes ?? 0) }}" placeholder="Minutes"> 
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="col-lg-6 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="repeat" id="repeat-task" data-bs-toggle="collapse" data-bs-target="#repeatSection">
                        <label class="form-check-label" for="repeat-task">Repeat</label>
                    </div>
                    <div class="collapse" id="repeatSection">
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label class="form-label">Repeat every</label>
                                <div class="input-group">
                                    <input type="number" name="repeat_count" class="form-control"
                                      value="{{ old('repeat_count', $duplicateTask->repeat_count ?? 1) }}">
                                    
                                    <select name="repeat_type" class="form-select">
                                        <option value="day" {{ old('repeat_type', $duplicateTask->repeat_type ?? '') == 'day' ? 'selected' : '' }}>Day(s)</option>
                                        <option value="week" {{ old('repeat_type', $duplicateTask->repeat_type ?? '') == 'week' ? 'selected' : '' }}>Week(s)</option>
                                         <option value="month" {{ old('repeat_type', $duplicateTask->repeat_type ?? '') == 'month' ? 'selected' : '' }}>Month</option>
                                        <option value="year" {{ old('repeat_type', $duplicateTask->repeat_type ?? '') == 'year' ? 'selected' : '' }}>Year</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cycles</label>
                                <input type="number" name="repeat_cycles" class="form-control"
                                 value="{{ old('repeat_cycles', $duplicateTask->repeat_cycles ?? 1) }}">
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="col-lg-6 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="dependent" id="dependent-task" data-bs-toggle="collapse" data-bs-target="#dependentSection">
                        <label class="form-check-label" for="dependent-task">Task is dependent on another task</label>
                    </div>
                    <div class="collapse" id="dependentSection">
                        <label class="form-label mt-2">Select Dependent Task</label>
                        <select name="dependent_task_id" class="form-select select-picker" data-live-search="true">
                            <option value="">--</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}"
                                    {{ old('dependent_task_id', $duplicateTask->dependent_task_id ?? '') == $task->id ? 'selected' : '' }}>
                                    {{ $task->title }} (Due: {{ \Carbon\Carbon::parse($task->due_date)->format('d-m-Y') }})
                                </option>

                            @endforeach
                        </select>
                    </div>
                </div>
            
                <!--<div class="col-lg-12 mt-4">-->
                <!--    <div class="form-group">-->
                <!--        <label for="file-upload-dropzone">Add File</label>-->
                <!--        <div class="dropzone border rounded" id="file-upload-dropzone">-->
                <!--            <div class="dz-default dz-message">-->
                <!--                <button class="dz-button" type="button">Choose a file</button>-->
                <!--            </div>-->
                <!--        </div>-->
                <!--        <input type="hidden" name="image_url" id="image_url">-->
                <!--    </div>-->
                <!--</div>-->
                
                <div class="col-lg-12 mt-4">
                        <div class="form-group">
                            <label for="task_file">Add File</label>
                            <input type="file" class="form-control" name="image_url" id="image_url">
                            
                            @error('image_url')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
            </div>


                <br>
                
                <div class="col-12 d-flex justify-content-end gap-2">
                <button type="submit" name="action" value="save" class="btn btn-primary">Save</button>
                <button type="submit" name="action" value="save_add_more" class="btn btn-secondary">Save & Add More</button>
                
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Cancel</a>
            </div>

            </form>
        </div>
    </div>

    <!-- Modal for Adding New Label -->

      <div class="modal fade" id="taskLabelsModal" tabindex="-1" aria-labelledby="taskLabelsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <form action="{{ route('labels.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Task Labels</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">

          {{-- Form Section --}}
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label">Label Name *</label>
              <input type="text" class="form-control" name="name" placeholder="Enter a label title" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">Color Code *</label>
              <input type="color" class="form-control form-control-color" name="color" value="#69D100" required>
            </div>

            <div class="col-md-5">
              <label class="form-label">Project</label>
              <select name="project_id" class="form-select">
                <option value="">-- None --</option>
                @foreach($projects as $proj)
                  <option value="{{ $proj->id }}"
                    {{ old('project_id', $duplicateTask->project_id ?? '') == $proj->id ? 'selected' : '' }}>
                    {{ $proj->name }}
                </option>

                @endforeach
              </select>
            </div>

            <div class="col-md-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
          </div>

          {{-- Table Section --}}
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Label Name</th>
                  <th>Color Code</th>
                  <th>Description</th>
                  <th>Project</th>
                  <th width="100">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($labels as $index => $label)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $label->label_name }}</td>
                    <td><span class="badge" style="background-color: {{ $label->color }}">{{ $label->color }}</span></td>
                    <td>{{ $label->description }}</td>
                    <td>{{ $label->project->name ?? '-' }}</td>
                    <td>
                      {{-- Delete button --}}
                      <form method="POST" action="{{ route('labels.destroy', $label->id) }}" onsubmit="return confirm('Delete this label?');">
                        @csrf 
                        <button class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
                @if($labels->isEmpty())
                  <tr><td colspan="6" class="text-center">No labels added yet.</td></tr>
                @endif
              </tbody>
            </table>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Add Label</button>
        </div>
      </div>
    </form>
  </div>
</div>


      <!-- Task Category Modal -->
      <div class="modal fade" id="taskCategoryModal" tabindex="-1" aria-labelledby="taskCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="{{ route('task-categories.store') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Task Categories</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">

          {{-- Table --}}
          <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Category Name</th>
                  <th width="100">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($taskCategories as $index => $category)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $category->category_name }}</td>
                    <td>
                      <form method="POST" action="{{ route('task-categories.force-delete', $category->id) }}">
                               @csrf

                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
                @if($taskCategories->isEmpty())
                  <tr><td colspan="3" class="text-center">No categories added yet.</td></tr>
                @endif
              </tbody>
            </table>
          </div>

          {{-- Add Category Form - Same Line --}}
          <div class="row align-items-end g-2">
            <div class="col-md-9">
              <label class="form-label">Category Name *</label>
              <input type="text" class="form-control" name="category_name" placeholder="Enter a category name" required>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100 mt-2">Save</button>
            </div>
          </div>

        </div>
      </div>
    </form>
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
                 <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
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

          
           
           <!--<div class="col-md-4 mb-3">-->
           <!--     <label class="form-label fw-semibold">Country <sup class="text-danger">*</sup></label>-->
            
           <!--     <div class="input-group input-group-sm shadow-sm">-->
                    <!-- Search box -->
           <!--         <input type="text" id="countrySearch" class="form-control border-end-0" placeholder="Search country...">-->
            
                    <!-- Country dropdown -->
           <!--         <select name="country" id="country" class="form-select form-select-sm">-->
           <!--             <option value="">Select</option>-->
           <!--             @foreach($countries as $country)-->
           <!--                 <option value="{{ $country->name }}">{{ $country->name }}</option>-->
           <!--             @endforeach-->
           <!--         </select>-->
           <!--     </div>-->
           <!-- </div>-->
           
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

           
            
            <!--<div class="col-md-4 mb-3">-->
            <!--    <label>Language</label>-->
               
            <!--        <select name="language" id="locale" data-live-search="true" class="form-control select-picker" data-size="8">-->
            <!--            <option value="en" data-content="<span class='flag-icon flag-icon-gb flag-icon-squared'></span> English">English</option>-->
            <!--            <option value="bn" data-content="<span class='flag-icon flag-icon-bd flag-icon-squared'></span> Bengali">Bengali</option>-->
            <!--            <option value="hi" data-content="<span class='flag-icon flag-icon-in flag-icon-squared'></span> Hindi">Hindi</option>-->
            <!--            <option value="fr" data-content="<span class='flag-icon flag-icon-fr flag-icon-squared'></span> French">French</option>-->
            <!--            <option value="de" data-content="<span class='flag-icon flag-icon-de flag-icon-squared'></span> German">German</option>-->
            <!--        </select>-->
                
            <!--</div>-->
            
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

        
        </main>


@push('scripts')

<script>
    $(function () {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select an option',
            allowClear: true
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#noDue').on('change', function () {
            const isChecked = $(this).is(':checked');

            const dueDateInput = $('input[name="due_date"]');

            if (isChecked) {
                dueDateInput.closest('div').hide(); // hide the input only
                dueDateInput.prop('disabled', true); // optional: disable it so it's not submitted
            } else {
                dueDateInput.closest('div').show();
                dueDateInput.prop('disabled', false);
            }
        }).trigger('change'); // trigger on page load to handle old values
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            new bootstrap.Popover(popoverTriggerEl);
        });
    });
</script>
<script>


    $(function () {
        $('[data-toggle="popover"]').popover();

        $('#set_time_estimate').on('change', function () {
            $('#set-time-estimate-fields').toggle(this.checked);
        });

        $('#repeat-task').on('change', function () {
            $('#repeat-fields').toggle(this.checked);
        });

        $('#dependent-task').on('change', function () {
            $('#dependent-fields').toggle(this.checked);
        });
    });
</script>
@endpush

@endsection
