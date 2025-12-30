{{-- Comment Form --}}
<div class="card shadow-sm mt-4">
    <div class="card-body">
    <form action="{{ route('task-comments.store', $task->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <textarea name="comment" class="form-control" rows="3" placeholder="Add a comment..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    
     <hr>
                                @if($task->comments->count())
                                    <ul class="list-group">
                                        @foreach($task->comments as $comment)
                                            <li class="list-group-item">
                                                <strong>{{ $comment->user->name ?? 'Unknown' }}:</strong>
                                                <div>{{ $comment->comment }}</div>
                                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="text-muted">No comments yet.</div>
                                @endif
    
     </div>
</div>