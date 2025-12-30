<div class="card shadow-sm mt-4">
    <div class="card-body">
        @forelse($history as $log)
            <div class="mb-3 border-bottom pb-2">
                <strong>{{ $log['user'] }}</strong><br>
                {{ $log['description'] }}
                
                @if(!empty($log['subtask']))
                    <br><span class="text-muted small">Subtask: {{ $log['subtask'] }}</span>
                @endif

                <br><small class="text-muted">{{ \Carbon\Carbon::parse($log['created_at'])->format('d-m-Y h:i A') }}</small>
            </div>
        @empty
            <p class="text-muted">No activity found.</p>
        @endforelse
    </div>
</div>
