<!-- View Modal -->
<div class="modal fade" id="viewSubTaskModal{{ $subtask->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $subtask->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sub Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-borderless small">
                    <tr>
                        <td width="25%" class="text-muted">Title</td>
                        <td>{{ $subtask->title }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Start Date</td>
                        <td>{{ \Carbon\Carbon::parse($subtask->start_date)->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Due Date</td>
                        <td>{{ \Carbon\Carbon::parse($subtask->due_date)->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Assigned To</td>
                        <td>
                            @if($subtask->assignee)
                                <img src="{{ asset($subtask->assignee->profile_image ?? 'admin/uploads/profile-images/default.png') }}"
                                     class="rounded-circle me-2" width="30" height="30" alt="{{ $subtask->assignee->name }}">
                                <strong>{{ $subtask->assignee->name }}</strong>
                                @if($subtask->assignee->id == auth()->id())
                                    <span class="badge bg-secondary">It's you</span>
                                @endif
                                <div class="text-muted small">{{ $subtask->assignee->designation ?? '' }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Description</td>
                        <td>{{ $subtask->description ?? '--' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Attachment</td>
                        <td>
                            @if($subtask->file)
                                <a href="{{ asset($subtask->file) }}" target="_blank">
                                    <i class="bi bi-paperclip"></i> {{ basename($subtask->file) }}
                                </a>
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td>{{ $subtask->created_at->diffForHumans() }}</td>
                    </tr>
                </table>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
