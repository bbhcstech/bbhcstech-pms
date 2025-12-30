@extends('admin.layout.app')

@section('content')
<div class="container">

    <br>
    <a href="{{ route('projects.index') }}" class="btn btn-secondary mb-3">← Back to Projects</a>

    {{-- Sub-navigation --}}
  
         <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('projects.show', $project->id) }}">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('project-members.index', $project->id)}}">Members</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('project-files.index', $project->id)}}">Files</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('milestones.index', $project->id)}}">Milestones</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.tasks.index', $project->id) }}">Tasks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.tasks.board', $project->id) }}">Task Board</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.gantt', $project->id) }}">Gantt Chart</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.timelogs.index', $project->id) }}">Timesheet</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('expenses.index', $project->id) }}">Expenses</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Notes</a>
            </li>
        
            {{-- Toggle Button --}}
            <li class="nav-item">
                <a class="nav-link text-primary" href="#" id="toggle-more">More ▾</a>
            </li>
        </ul>
        
        {{-- Collapsible Extra Tabs --}}
        <ul class="nav nav-tabs mb-4 d-none" id="more-tabs">
          
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('projects.discussions.index', $project->id) }}" >Discussion</a>
            </li>
            
            <li class="nav-item">
               <a class="nav-link" href="{{ route('projects.burndown', $project->id) }}">Burndown Chart</a>

            </li>
            
            <li class="nav-item">
               <a class="nav-link" href="{{ route('admin.activities.project', $project->id) }}">Activity</a>


            </li>
            
             <li class="nav-item">
               <a class="nav-link" href="{{ route('tickets.index') }}">Ticket</a>


            </li>
            {{-- Add more optional tabs here if needed --}}
        </ul>

    <h4 class="mb-3">Edit Project Note ({{ $project->name }})</h4>

    <form method="POST" action="{{ route('projects.notes.update', [$projectId, $note->id]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Note Title *</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $note->title) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Note Type</label>
            <select name="type" class="form-select" id="note-type">
                <option value="0" {{ $note->type == 0 ? 'selected' : '' }}>Public</option>
                <option value="1" {{ $note->type == 1 ? 'selected' : '' }}>Private</option>
            </select>
        </div>

        <div class="mb-3 {{ $note->type == 1 ? '' : 'd-none' }}" id="employee-select">
            <label class="form-label">Employee *</label>
            <select name="employee_id" class="form-select">
                <option value="">Select</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $note->employee_id == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-2 {{ $note->type == 1 ? '' : 'd-none' }}" id="client-visible">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_client_show" id="is_client_show" value="1"
                        {{ $note->is_client_show ? 'checked' : '' }}>
                    <label class="form-check-label text-dark-grey" for="is_client_show">
                        Visible To Client
                    </label>
                </div>
            </div>

            <div class="col-md-6 mb-2 {{ $note->type == 1 ? '' : 'd-none' }}" id="password-visible">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="ask_password" id="ask_password" value="1"
                        {{ $note->ask_password ? 'checked' : '' }}>
                    <label class="form-check-label text-dark-grey" for="ask_password">
                        Ask to re-enter password
                    </label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Note Detail</label>
            <textarea name="details" rows="6" class="form-control" required>{{ old('details', $note->details) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('projects.notes.index', $project->id) }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

@push('js')
<script>
   
    $(document).ready(function () {
    $('#expenseTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
                search: "_INPUT_",
                searchPlaceholder: "Search tickets..."
        }
    });
  });
</script>

<script>
    document.getElementById('toggle-more').addEventListener('click', function(e) {
        e.preventDefault();
        const moreTabs = document.getElementById('more-tabs');
        if (moreTabs.classList.contains('d-none')) {
            moreTabs.classList.remove('d-none');
            this.innerHTML = 'Less ▴';
        } else {
            moreTabs.classList.add('d-none');
            this.innerHTML = 'More ▾';
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const noteTypeSelect = document.getElementById('note-type');
    const employeeSelectDiv = document.getElementById('employee-select');

    noteTypeSelect.addEventListener('change', function () {
        if (this.value == '1') {
            employeeSelectDiv.classList.remove('d-none');
        } else {
            employeeSelectDiv.classList.add('d-none');
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const noteTypeSelect = document.getElementById('note-type');
    const employeeSelectDiv = document.getElementById('employee-select');
    const clientVisibleDiv = document.getElementById('client-visible');

    noteTypeSelect.addEventListener('change', function () {
        if (this.value == '1') {
            // Private selected
            employeeSelectDiv.classList.remove('d-none');
            clientVisibleDiv.classList.remove('d-none');
        } else {
            // Public selected
            employeeSelectDiv.classList.add('d-none');
            clientVisibleDiv.classList.add('d-none');
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const noteTypeSelect = document.getElementById('note-type');
    const employeeSelectDiv = document.getElementById('employee-select');
    const clientVisibleDiv = document.getElementById('client-visible');
    const passwordVisibleDiv = document.getElementById('password-visible');

    noteTypeSelect.addEventListener('change', function () {
        const isPrivate = this.value == '1';

        employeeSelectDiv.classList.toggle('d-none', !isPrivate);
        clientVisibleDiv.classList.toggle('d-none', !isPrivate);
        passwordVisibleDiv.classList.toggle('d-none', !isPrivate);
    });

    // Trigger change on page load in case of edit form
    noteTypeSelect.dispatchEvent(new Event('change'));
});

</script>
@endpush
