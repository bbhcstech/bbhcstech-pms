{{-- ðŸ”¹ Sub Tasks Section --}}
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h5 class="mb-3">Sub Tasks</h5>

        {{-- Form to Add Subtask --}}
       @php
    $agents = \App\Models\User::where('role', 'employee')->get();
@endphp

            <form method="POST" action="{{ route('subtasks.store', $task->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="task_id" value="{{ $task->id }}">
            
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="title" placeholder="Sub Task Title *" required>
                    </div>
            
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="start_date">
                    </div>
            
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="due_date">
                    </div>
            
                    <div class="col-md-2">
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Assign To</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            
                <div class="row mb-3">
                    <div class="col-md-10">
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional Description"></textarea>
                    </div>
            
                    <div class="col-md-2">
                        <input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx">
                    </div>
                </div>
            
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Sub Task
                </button>
            </form>
            
            <!---sub task update modal----->
            @foreach($task->subTasks as $subtask)
            <div class="modal fade" id="editSubTaskModal{{ $subtask->id }}" tabindex="-1" aria-labelledby="editSubTaskModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form method="POST" action="{{ route('subtasks.update', $subtask->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="task_id" value="{{ $task->id }}">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Sub Task</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="title" value="{{ $subtask->title }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" name="start_date" value="{{ $subtask->start_date }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" name="due_date" value="{{ $subtask->due_date }}">
                                    </div>
                                    <div class="col-md-2">
                                        <select name="assigned_to" class="form-select" required>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}" {{ $subtask->assigned_to == $agent->id ? 'selected' : '' }}>
                                                    {{ $agent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
            
                                <div class="row mb-3">
                                    <div class="col-md-10">
                                        <textarea name="description" class="form-control" rows="2">{{ $subtask->description }}</textarea>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="file" name="file" class="form-control">
                                        @if($subtask->files)
                                            <small class="text-muted">Current: <a href="{{ asset('/' . $subtask->files) }}" target="_blank">File</a></small>
                                        @endif
                                        
                                     
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- View Sub Task Modal -->
           <div class="modal fade" id="viewSubTaskModal{{ $subtask->id }}" tabindex="-1" aria-labelledby="viewSubTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sub Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Title -->
                <div class="mb-3">
                    <strong>Title</strong><br>
                    {{ $subtask->title }}
                </div>

                <!-- Start Date -->
                <div class="mb-3">
                    <strong>Start Date</strong><br>
                    {{ \Carbon\Carbon::parse($subtask->start_date)->format('d-m-Y') ?? '--' }}
                </div>

                <!-- Due Date -->
                <div class="mb-3">
                    <strong>Due Date</strong><br>
                    {{ \Carbon\Carbon::parse($subtask->due_date)->format('d-m-Y') ?? '--' }}
                </div>

                <!-- Assigned To -->
                <div class="mb-3">
                    <strong>Assigned To</strong><br>
                    @if($subtask->assignee)
                        {{ $subtask->assignee->name }}
                        @if($subtask->assignee->id === auth()->id())
                            <br><small class="text-success">{{ $subtask->assignee->gender == 'female' ? 'Mrs' : 'Mr' }} {{ $subtask->assignee->name }} – It's you</small>
                        @endif
                        <br><small class="text-muted">{{ $subtask->assignee->designation ?? '' }}</small>
                    @else
                        <span class="text-muted">Unassigned</span>
                    @endif
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <strong>Description</strong><br>
                    {!! nl2br(e($subtask->description ?? '--')) !!}
                </div>

                <!-- File (if any) -->
                @if($subtask->files)
                    <div class="mb-3">
                        <strong>Attachment</strong><br>
                        <span>{{ basename($subtask->files) }}</span>
                        <div class="mt-2">
                            <a href="{{ asset($subtask->files) }}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="{{ asset($subtask->files) }}" download class="btn btn-sm btn-outline-success me-1">
                                <i class="bi bi-download"></i> Download
                            </a>
                            <form action="{{ route('subtask.file.delete', $subtask->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete file?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

            
            @endforeach



        <hr>

        {{-- Sub Tasks List --}}
        @if($task->subTasks && $task->subTasks->count())
            <table class="table table-bordered mt-3">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Start</th>
                        <th>Due</th>
                        <th>Description</th>
                        <th>File</th>
                        <th>Assign to</th>
                        <th>Action</th>
                    </tr>
                </thead>
               <tbody>
                @foreach($task->subTasks as $index => $subtask)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $subtask->title }}</td>
                    <td>
                        <span class="badge bg-{{ $badgeColors[$subtask->status] ?? 'secondary' }}">
                            {{ $subtask->status }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($subtask->start_date)->format('d-m-Y') ?? '--' }}</td>
                    <td>{{ \Carbon\Carbon::parse($subtask->due_date)->format('d-m-Y') ?? '--' }}</td>
                    <td>{{ $subtask->description ?? '--' }}</td>
                    <td>
                        @if($subtask->files)
                            <a href="{{ asset('/' . $subtask->files) }}" target="_blank">
                                <i class="bi bi-paperclip"></i> View File
                            </a>
                        @endif
                    </td>
                    <td>
                        @if($subtask->assignee)
                            <img src="{{ asset($subtask->assignee->profile_image ?? 'admin/uploads/profile-images/default.png') }}"
                                 alt="{{ $subtask->assignee->name }}"
                                 class="rounded-circle" width="24" height="24">
                            <span class="ms-1">{{ $subtask->assignee->name }}</span>
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewSubTaskModal{{ $subtask->id }}">
                                        <i class="bi bi-eye"></i> View Details
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editSubTaskModal{{ $subtask->id }}">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('subtasks.destroy', $subtask->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                
                
                @endforeach
                </tbody>

            </table>
        @else
            <div class="alert alert-warning mt-3 mb-0">No sub-tasks added yet.</div>
        @endif
    </div>
</div>
