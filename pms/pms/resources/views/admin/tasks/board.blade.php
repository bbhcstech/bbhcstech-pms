@extends('admin.layout.app')

@section('content')
<main class="main">
    <div class="container">
        @if(isset($project))
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
               <a class="nav-link active" href="{{ route('projects.tasks.index', $project->id)}}">Tasks</a>
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
               <a class="nav-link" href="{{ route('tickets.index', ['project_id' => $project->id]) }}">Ticket</a>


            </li>
                {{-- Add more optional tabs here if needed --}}
            </ul>
        @endif

        <div class="mb-3 alert alert-warning">
            <strong>Note:</strong> You cannot move the task to or from the 'Waiting for Approval' column. To update the task status, please go to the <strong>Tasks</strong> menu.
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Task Board - {{ $project->name ?? 'All Projects' }}</h4>
        </div>

        <div class="row">
            @php
                $statuses =  ['Waiting for Approval','To Do', 'Doing','Incomplete','Completed'];
            @endphp

            @foreach($statuses as $status)
                <div class="col-md-3 mb-4">
                    <div class="card shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>{{ $status }}</strong>
                            @if($status !== 'Waiting for Approval')
                               <!--<button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal" onclick="setTaskStatus('{{ $status }}')">Add Task</button>-->
                               <a href="{{ route('tasks.create') }}?project_id={{ $project->id }}" class="btn btn-primary">Add Task</a>
                            @endif
                        </div>
                        <div class="card-body" style="min-height: 150px;">
                            @php $count = $tasks->where('status', $status)->count(); @endphp
                            <p><strong>{{ $count }}</strong> Task{{ $count !== 1 ? 's' : '' }}</p>

                             <div class="task-column" data-status="{{ $status }}" ondrop="drop(event)" ondragover="allowDrop(event)" id="column-{{ Str::slug($status) }}">

                                @foreach($tasks->where('status', $status) as $task)
                                    <div class="card mb-2 p-2 shadow-sm task-item"
                                         id="task-{{ $task->id }}"
                                         draggable="true"
                                         ondragstart="drag(event)">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong>{{ $task->title }}</strong>
                                            @if($task->assignee)
                                                <img src="{{ asset($task->assignee->profile_image ?? 'default-avatar.png') }}"
                                                     class="rounded-circle"
                                                     width="30" height="30"
                                                     title="{{ $task->assignee->name }}">
                                            @endif
                                        </div>
                                        <div class="text-muted small mb-1">
                                            {{ Str::limit($task->description, 50) }}
                                        </div>
                                        <div class="text-muted small">
                                            Created: {{ $task->created_at->format('d M, Y') }}
                                        </div>
                                    </div>
                                @endforeach

                          

                            </div>


                                
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Add Task Modal --}}
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <form action="{{ route('tasks.store') }}" method="POST">
    @csrf
    <input type="hidden" name="status" id="taskStatusInput">
    
    @if(isset($project))
    <input type="hidden" name="redirect_to_board" value="yes">
@endif

    <div class="modal-header">
        <h5 class="modal-title" id="addTaskModalLabel">Add Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        {{-- Project --}}
        <div class="mb-3">
            @if(isset($project))
                <label class="form-label">Project</label>
                <input type="text" class="form-control" value="{{ $project->name }}" readonly>
                <input type="hidden" name="project_id" value="{{ $project->id }}">
            @else
                <label class="form-label">Select Project</label>
                <select name="project_id" class="form-control" required>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        {{-- Parent Task --}}
        <div class="mb-3">
            <label>Parent Task (optional)</label>
            <select name="parent_id" class="form-control">
                <option value="">None</option>
                @foreach($tasks as $t)
                    <option value="{{ $t->id }}">{{ $t->title }}</option>
                @endforeach
            </select>
        </div>

        {{-- Assign To --}}
        <div class="mb-3">
            <label>Assign To</label>
            <select name="assigned_to" class="form-control">
                <option value="">Unassigned</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Title --}}
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        {{-- Description --}}
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Create Task</button>
    </div>
</form>

            </div>
        </div>
    </div>
</main>


@endsection

@section('js')
<script>
    console.log("✅ JS loaded successfully!");
</script>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log("✅ DOM loaded, JS initialized!");

        window.allowDrop = function(ev) {
            ev.preventDefault();
        }

        window.drag = function(ev) {
            console.log("Dragging:", ev.target.id);
            ev.dataTransfer.setData("text", ev.target.id);
        }

        window.drop = function(ev) {
            ev.preventDefault();
            const taskId = ev.dataTransfer.getData("text").replace('task-', '');
            const newStatus = ev.currentTarget.getAttribute('data-status');

            console.log("Dropped Task ID:", taskId, "→", newStatus);

            if (newStatus === 'Waiting for Approval') {
                alert("Cannot move task into 'Waiting for Approval'.");
                return;
            }

            const draggedEl = document.getElementById('task-' + taskId);
            if (!draggedEl) {
                console.error("Dragged element not found!");
                return;
            }

            ev.currentTarget.appendChild(draggedEl);

            const updateRoute = "{{ route('tasks.updateStatus', ['task' => 'TASK_ID']) }}".replace('TASK_ID', taskId);

            fetch(updateRoute, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert("Failed to update status: " + (data.message ?? "Unknown error"));
                } else {
                    console.log("✅ Task status updated successfully.");
                }
            })
            .catch(error => {
                console.error("Error updating task:", error);
                alert("Something went wrong.");
            });
        }

        window.setTaskStatus = function(status) {
            document.getElementById('taskStatusInput').value = status;
        }
    });
</script>

@push('scripts')
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
