@extends('admin.layout.app')

@section('content')
<div class="container-fluid mt-4">
    
    
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <!-- Left: Add Project Button -->
    <h4 class="mb-4">Archived Projects</h4>

    <!-- Right: Icon Buttons -->
    <div class="btn-group mb-2" role="group">
        <!-- Projects -->
        <a href="{{ route('projects.index') }}" class="btn btn-secondary f-14 btn-active projects" 
           data-bs-toggle="tooltip" data-bs-placement="top" title="Projects">
           <i class="bi bi-list-ul"></i>
        </a>

        <!-- Archive -->
        <a href="{{ route('projects.archive') }}" class="btn btn-secondary f-14" 
           data-bs-toggle="tooltip" data-bs-placement="top" title="Archive">
           <i class="bi bi-archive"></i>
        </a>
        
        <!-- Calendar -->
        <a href="{{ route('projects.calendar') }}" class="btn btn-secondary f-14" 
           data-bs-toggle="tooltip" data-bs-placement="top" title="Calendar">
           <i class="bi bi-calendar"></i>
        </a>

    </div>
</div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover table-bordered" id="archive-projects-table">
                <thead>
                    <tr>
                        <th>Project Name</th>
                         <th>Members</th>
                         <th>Deadline</th>
                        <th>Client</th>
                        <th>Completions</th>
                        <th>Status</th>
                        <!--<th>Progress</th>-->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td>{{ $project->name }}</td>
                            <td style="white-space: nowrap;" >
                                @foreach($project->users as $user)
                                    <span class="badge bg-primary me-1">{{ $user->name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $project->deadline }}</td>
                            <td>{{ $project->client->name ?? '--' }}</td>
                            <td>{{ $project->completion_percent }}</td>
                            <td>{{ ucfirst($project->status) }}</td>
                            <!--<td>{{ $project->progress }}%</td>-->
                            <td>
                                <div class="d-flex">
                                    <!-- Restore Form -->
                                    <form action="{{ route('projects.restore', $project->id) }}" method="POST" class="me-2">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Restore this project?')">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>

                                    <!-- Permanent Delete Form -->
                                    <form action="{{ route('projects.forceDelete', $project->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Permanently delete this project?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#archive-projects-table').DataTable({
        dom: 'Bfrtip',
        buttons: ['excel'],
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
@endpush

