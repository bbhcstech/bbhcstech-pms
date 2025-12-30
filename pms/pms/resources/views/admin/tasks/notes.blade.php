<div class="card shadow-sm mt-4">
    <div class="card-body">
    <h6 class="fw-bold mb-3">Add Note</h6>

    <form action="{{ route('tasks.notes.store', $task->id) }}" method="POST">
        @csrf
        <textarea name="note" class="form-control mb-2" rows="3" placeholder="Enter your note..." required></textarea>
        <button type="submit" class="btn btn-primary">Add Note</button>
    </form>


@if($task->notes && $task->notes->count())
    <h6 class="fw-bold mt-4">All Notes</h6>
    <ul class="list-group">
        @foreach($task->notes as $note)
            <li class="list-group-item">
                <strong>{{ $note->user->name ?? 'Unknown' }}:</strong><br>
                <p class="mb-1">{{ $note->note }}</p>
                <small class="text-muted">{{ $note->created_at->format('d-m-Y h:i A') }}</small>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-muted">No notes added yet.</p>
@endif

</div>
</div>
