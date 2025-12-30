@extends('admin.layout.app')

@section('content')

<div class="container">
    
      <br>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary mb-3">← Back to Projects</a>

             {{-- Sub-navigation bar --}}
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
                <a class="nav-link" href="{{ route('projects.notes.index', $project->id) }}">Notes</a>
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
               <a class="nav-link" href="{{ route('tickets.index', ['project_id' => $project->id]) }}">Ticket</a>


            </li>
                {{-- Add more optional tabs here if needed --}}
            </ul>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Project Note Details - {{ $project->name }}</h4>
        <a href="{{ route('projects.notes.create', $project->id) }}" class="btn btn-primary">Add Note</a>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    

    <table id="noteTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Detail</th>
                <th>Added By</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notes as $note)
            <tr>
                <td>{{ $note->title }}</td>
                <td>{{ $note->type ? 'Private' : 'Public' }}</td>
                <td>{!! Str::limit($note->details, 100) !!}</td>
                <td>{{ $note->addedBy->name ?? 'N/A' }}</td>
                <td>{{ $note->created_at->format('d M Y') }}</td>
                  <td>
                <a href="{{ route('projects.notes.noteshow', [$note->project_id, $note->id]) }}" class="btn btn-sm btn-info">View</a>

                @if($note->type == 0 || Auth::id() == $note->added_by)
                    <a href="{{ route('projects.notes.edit', [$note->project_id, $note->id]) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('projects.notes.destroy', [$note->project_id, $note->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                @endif
            </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('js')
<script>
   
    $(document).ready(function () {
    $('#noteTable').DataTable({
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
@endpush