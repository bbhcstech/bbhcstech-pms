@extends('admin.layout.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* keep select2 original <select> focusable (avoid display:none) */
.select2-hidden-accessible {
  position: absolute !important;
  left: -9999px !important;
  top: auto !important;
  width: 1px !important;
  height: 1px !important;
  overflow: hidden !important;
}

/* small optional visual tweak for select2 with bootstrap5 */
.select2-container--bootstrap-5 .select2-selection {
  min-height: calc(1.5em + .75rem + 2px);
  padding: .375rem .75rem;
  border-radius: .375rem;
}
</style>
@endpush

@section('content')
<main id="main" class="main py-4">
    <div class="container px-3">

        @if(isset($project))
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item"><a class="nav-link" href="{{ route('projects.show', $project->id) }}">Overview</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('project-members.index', $project->id)}}">Members</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('project-files.index', $project->id)}}">Files</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('milestones.index', $project->id)}}">Milestones</a></li>
                <li class="nav-item"><a class="nav-link active" href="{{ route('projects.tasks.index', $project->id)}}">Tasks</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('projects.tasks.board', $project->id) }}">Task Board</a></li>
            </ul>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Task</h5>
            </div>
            <div class="card-body">
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

               <!-- novalidate prevents browser native popups; we still validate server-side -->
               <form id="taskForm" method="POST" action="{{ route('tasks.update', $task->id) }}" enctype="multipart/form-data" novalidate>
                   @csrf
                   @method('PUT')

                   @include('admin.tasks.form', [
                       'task' => $task,
                       'projects' => $projects,
                       'project' => $project ?? null,
                       'taskCategories' => $taskCategories,
                       'labels' => $labels,
                       'users' => $users,
                       'tasks' => $tasks,
                       'departments'=> $departments, 
                       'designations' => $designations,
                       'countries' => $countries,
                       'employee' => $employee,
                       'prtdepartments' => $prtdepartments,
                       'buttonText' => 'Update'
                   ])
               </form>

            </div>
        </div>
    </div>
</main>
@endsection

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
              <label class="form-label">Label Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" placeholder="Enter a label title" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">Color Code <span class="text-danger">*</span></label>
              <input type="color" class="form-control form-control-color" name="color" value="#69D100" required>
            </div>

            <div class="col-md-5">
              <label class="form-label">Project <span class="text-danger">*</span></label>
              <select name="project_id" class="form-select">
                <option value="">-- None --</option>
                @foreach($projects as $proj)
                  <option value="{{ $proj->id }}">{{ $proj->name }}</option>
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

@push('scripts')
<!-- include jQuery and select2 (if your layout already includes them, you can remove these) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function () {
    // init select2 on elements with .select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select an option',
        allowClear: true
    });

    // existing toggles (preserve behavior)
    $('#set_time_estimate').on('change', function () { $('#set-time-estimate-fields').toggle(this.checked); }).trigger('change');
    $('#repeat-task').on('change', function () { $('#repeat-fields').toggle(this.checked); }).trigger('change');
    $('#dependent-task').on('change', function () { $('#dependent-fields').toggle(this.checked); }).trigger('change');

    // due date handling
    $('#noDue').on('change', function () {
        const isChecked = $(this).is(':checked');
        const dueDateInput = $('input[name="due_date"]');
        if (isChecked) {
            dueDateInput.hide().prop('disabled', true);
        } else {
            dueDateInput.show().prop('disabled', false);
        }
    }).trigger('change');

    // helper: find required inputs that are hidden (would block browser validation)
    function findHiddenRequiredInputs($form) {
        return $form.find(':input[required]').filter(function () {
            const $el = $(this);
            // treat as not focusable if not visible or CSS hides it
            return !$el.is(':visible') || $el.css('display') === 'none' || $el.css('visibility') === 'hidden';
        });
    }

    // Before submit: disable hidden required inputs so browser won't block submission
    $('#taskForm').on('submit', function (e) {
        const $form = $(this);
        const blockers = findHiddenRequiredInputs($form);
        if (blockers.length) {
            console.warn('Disabling hidden required inputs before submit:', blockers.map(function(i,el){ return el.name || el.id || el; }).get());
            blockers.prop('disabled', true);
        }
        // also disable hidden file inputs
        $form.find('input[type="file"]').filter(':hidden').prop('disabled', true);
        // If you need client-side checks for visible widgets (mobile/user_role/department etc), add them here and call e.preventDefault() on failure.
    });

    // Re-enable disabled inputs when page is shown (back navigation or after server redirect)
    $(window).on('pageshow', function () {
        $(':input:disabled').prop('disabled', false);
    });

    // init bootstrap popovers (support both data-toggle and data-bs-toggle)
    var popoverList = [].slice.call(document.querySelectorAll('[data-toggle="popover"], [data-bs-toggle="popover"]'));
    popoverList.map(function (el) { return new bootstrap.Popover(el); });
});
</script>
@endpush
