<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h5 class="mb-3">Timesheet</h5>
        <table class="table table-bordered small">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Memo</th>
                    <th>Hours Logged</th>
                </tr>
            </thead>
            <tbody>
                @forelse($task->tasktimeLogs as $log)
                    <tr>
                        <td>{{ $log->user->name ?? '--' }}</td>
                        <td>{{ \Carbon\Carbon::parse($log->start_time)->format('d-m-Y h:i A') }}</td>
                        <td>{{ $log->end_time ? \Carbon\Carbon::parse($log->end_time)->format('d-m-Y h:i A') : '--' }}</td>
                        <td>{{ $log->memo ?? '--' }}</td>
                        <td>
                            @if($log->end_time)
                                @php
                                    $duration = \Carbon\Carbon::parse($log->start_time)->diff(\Carbon\Carbon::parse($log->end_time));
                                    $hours = $duration->h;
                                    $minutes = $duration->i;
                                    $seconds = $duration->s;
                                @endphp
                                {{ $hours > 0 ? $hours . 'h ' : '' }}
                                {{ $minutes > 0 ? $minutes . 'm ' : '' }}
                                {{ $seconds > 0 ? $seconds . 's' : '' }}
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
