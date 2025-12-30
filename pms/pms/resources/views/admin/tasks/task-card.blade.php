<div class="card mb-2 p-2 shadow-sm task-item"
     id="task-{{ $task->id }}"
     draggable="true"
     ondragstart="drag(event)">
    <div class="d-flex justify-content-between align-items-center">
        <strong>{{ $task->title }}</strong>
        @if($task->assignedUser)
            <img src="{{ asset($task->assignedUser->profile_image ?? 'default-avatar.png') }}"
                 class="rounded-circle"
                 width="30" height="30"
                 title="{{ $task->assignedUser->name }}">
        @endif
    </div>
    <div class="text-muted small mb-1">
        {{ Str::limit($task->description, 50) }}
    </div>
    <div class="text-muted small">
        Created: {{ $task->created_at->format('d M, Y') }}
    </div>
</div>
