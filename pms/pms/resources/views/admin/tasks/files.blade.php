<div class="card shadow-sm mt-4">
    <div class="card-body">
        
        <form action="{{ route('tasks.uploadFile', $task->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <input type="file" name="attachment" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
        
         @if($task->image_url)
                                    <div class="mb-3">
                                        <strong>Uploaded File:</strong><br>
                                        @if(Str::contains($task->image_url, ['.jpg', '.jpeg', '.png', '.gif']))
                                            <img src="{{ asset($task->image_url) }}" alt="Task File" class="img-thumbnail mt-2" width="200">
                                        @else
                                            <a href="{{ asset($task->image_url) }}" target="_blank">Download File</a>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-muted">No file uploaded yet.</p>
                                @endif

</div>
</div>
