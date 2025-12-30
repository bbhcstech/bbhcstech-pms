@extends('admin.layout.app')

@section('content')
<main id="main" class="main">
    <div class="container">
        <br>
        <h5>Edit Project</h5>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">

                <div class="col-md-6">
                    <label>Short Code <sup class="text-danger">*</sup></label>
                    <input type="text" name="project_code" class="form-control"
                           value="{{ old('project_code', $project->project_code) }}"
                           placeholder="Project unique short code" required>
                </div>

                <div class="col-md-6">
                    <label>Project Name <sup class="text-danger">*</sup></label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $project->name) }}" required>
                </div>

                <div class="col-md-4">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control"
                           value="{{ old('start_date', $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}">
                </div>

                @php
                    $withoutDeadlineChecked = old('without_deadline', $project->without_deadline);
                @endphp

                <div class="col-md-4">
                    <label>Deadline</label>
                    <input type="date" name="deadline" id="deadline_input" class="form-control"
                           value="{{ old('deadline', $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('Y-m-d') : '') }}"
                           {{ $withoutDeadlineChecked ? 'disabled' : '' }}>
                </div>

                <div class="col-md-4 d-flex align-items-center pt-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="without_deadline" id="without_deadline"
                               {{ $withoutDeadlineChecked ? 'checked' : '' }} value="1">
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
                                <option value="{{ $cat->id }}" {{ old('category_id', $project->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->category_name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#catModal">
                            Add
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label>Department <sup class="text-danger">*</sup></label>
                    <select name="department_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $project->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->dpt_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Client</label>
                    <div class="input-group">
                        <select name="client_id" id="client_id" class="form-control">
                            <option value="">Select</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $project->client_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#clientModal">
                            Add
                        </button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label>Project Summary</label>
                    <textarea name="description" class="form-control">{{ old('description', $project->description) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control">{{ old('notes', $project->notes) }}</textarea>
                </div>

                <!-- Feature toggles as checkboxes (controller expects has()) -->
                <div class="col-md-3">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="public_gantt_chart" id="public_gantt_chart"
                               value="1" {{ old('public_gantt_chart', $project->public_gantt_chart) ? 'checked' : '' }}>
                        <label class="form-check-label" for="public_gantt_chart">Public Gantt Chart</label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="public_taskboard" id="public_taskboard"
                               value="1" {{ old('public_taskboard', $project->public_taskboard) ? 'checked' : '' }}>
                        <label class="form-check-label" for="public_taskboard">Public Task Board</label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="need_approval_by_admin" id="need_approval_by_admin"
                               value="1" {{ old('need_approval_by_admin', $project->need_approval_by_admin) ? 'checked' : '' }}>
                        <label class="form-check-label" for="need_approval_by_admin">Task approval required</label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="public" id="is_public" value="1"
                               {{ old('public', $project->public) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_public">Create Public Project</label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label>Add Project Members <sup class="text-danger">*</sup></label>
                    <div class="row">
                        <div class="col-md-9">
                            @php
                                $selectedMemberIds = old('employee_ids', $project->users->pluck('id')->toArray());
                            @endphp

                            <select name="employee_ids[]" id="employee_ids" class="form-control" multiple>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" data-fullname="{{ $u->name }}"
                                        {{ in_array($u->id, $selectedMemberIds) ? 'selected' : '' }}>
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

                <!-- Other Details -->
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

                                @if(!empty($project->project_file))
                                    <div class="mt-2">
                                        <p>Current File:</p>
                                        <a href="{{ asset($project->project_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            View File
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <!-- Currency -->
                                <div class="col-md-4 mb-3">
                                    <label for="currency" class="form-label">Currency</label>
                                    <select id="currency" name="currency_id" class="form-select">
                                        <option value="">Select</option>
                                        @foreach($currency as $c)
                                            <option value="{{ $c->id }}" {{ old('currency_id', $project->currency_id) == $c->id ? 'selected' : '' }}>
                                                {{ $c->currency_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Project Budget -->
                                <div class="col-md-4 mb-3">
                                    <label for="project_budget" class="form-label">Project Budget</label>
                                    <input type="number" class="form-control" id="project_budget" name="project_budget"
                                           placeholder="e.g. 10000" value="{{ old('project_budget', $project->project_budget) }}">
                                </div>

                                <!-- Hours Estimate -->
                                <div class="col-md-4 mb-3">
                                    <label for="hours_allocated" class="form-label">Hours Estimate (In Hours)</label>
                                    <input type="number" class="form-control" id="hours_allocated" name="hours_allocated"
                                           placeholder="e.g. 50" value="{{ old('hours_allocated', $project->hours_allocated) }}">
                                </div>
                            </div>

                            <!-- Checkboxes -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="manual_timelog" id="manual_timelog" value="1"
                                               {{ old('manual_timelog', $project->manual_timelog) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="manual_timelog">Allow manual time logs</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="enable_miroboard" id="miroboard_checkbox" value="1"
                                               {{ old('enable_miroboard', $project->enable_miroboard) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="miroboard_checkbox">Enable Miroboard</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="allow_client_notification" id="client_task_notification" value="1"
                                               {{ old('allow_client_notification', $project->allow_client_notification) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="client_task_notification">Send task notification to client</label>
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
                    <div class="modal-header">
                        <h5 class="modal-title">Project Categories</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
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

        <!-- Client Modal (unchanged) -->
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

        <!-- Employee Modal (keeps original content) -->
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
                            {{-- keep your employee form fields here as before --}}
                            <div class="text-start mt-3">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- Parent/Department modals unchanged -->
        {{-- prtModal and dptModal markup here (unchanged) --}}

    </div>
</main>
@endsection

@section('js')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css" />
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        // Employee multi-select
        $('#employee_ids').select2({
            placeholder: "Select Employees",
            allowClear: true,
            width: '100%'
        });

        // Ensure preselected values applied after select2 init
        const preselected = @json(old('employee_ids', $project->users->pluck('id')->toArray()));
        if (preselected && preselected.length) {
            $('#employee_ids').val(preselected).trigger('change');
        }

        // Client single-select
        $('#client_id').select2({
            placeholder: "Select Client",
            allowClear: true,
            width: '100%',
            tags: false
        });

        // Category and currency single selects
        $('#project_category_id, #currency').select2({
            placeholder: "Select",
            allowClear: true,
            width: '100%',
            tags: false
        });

        // Deadline toggle
        $('#without_deadline').on('change', function () {
            if ($(this).is(':checked')) {
                $('#deadline_input').prop('disabled', true).val('');
            } else {
                $('#deadline_input').prop('disabled', false);
            }
        });
    });
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

                  Swal.fire({ title: 'Success!', text: 'Project Category added successfully.', icon: 'success' });
              }
          },
          error: function(xhr) {
              $('#cat-error').removeClass('d-none').text(xhr.responseJSON.message || 'Error occurred.');
          }
      });
  });

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
                  ).trigger('change');

                  $('#addClientForm')[0].reset();
                  const modalEl = document.getElementById('clientModal');
                  const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                  modalInstance.hide();

                  Swal.fire({ title: 'Success!', text: 'Client added successfully.', icon: 'success' });
              }
          },
          error: function(xhr) {
              $('#client-error').removeClass('d-none').text(xhr.responseJSON.message || 'Error occurred.');
          }
      });
  });

  // Modal z-index stacking helper
  document.addEventListener("show.bs.modal", function (event) {
      const zIndex = 1050 + 10 * document.querySelectorAll('.modal.show').length;
      event.target.style.zIndex = zIndex;
      setTimeout(() => {
          const backdrops = document.querySelectorAll('.modal-backdrop');
          backdrops[backdrops.length - 1].style.zIndex = zIndex - 1;
      });
  });
</script>
@endsection
