@extends('admin.layout.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-3">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0">✏️ Edit Ticket</h5>
            <a href="{{ route('tickets.index') }}" class="btn btn-light btn-sm">← Back to List</a>
        </div>
        
         @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <form method="POST" action="{{ route('tickets.update', $ticket->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body row g-4">
                
                
                <div class="col-md-4">
                    <label class="form-label d-block mb-2">Requester <span class="text-danger">*</span></label>
                    <div class="d-flex">
                        <div class="form-check form-check-inline me-4">
                            <input type="radio" class="form-check-input" name="requester_type" id="requester-client" value="client" {{ $ticket->requester_type == 'client' ? 'checked' : '' }}>
                            <label class="form-check-label" for="requester-client">Client</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="requester_type" id="requester-employee" value="employee" {{ $ticket->requester_type == 'employee' ? 'checked' : '' }}>
                            <label class="form-check-label" for="requester-employee">Employee</label>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Requester Name <span class="text-danger">*</span></label>
                    <select name="requester_name" id="requester_name" class="form-select">
                        <option value="">Select Requester Name</option>
                
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                    data-type="client"
                                    {{ $ticket->requester_type == 'client' && $ticket->requester_id == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}"
                                    data-type="employee"
                                    {{ $ticket->requester_type == 'employee' && $ticket->requester_id == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>


               
                <div class="col-md-4">
                    <label class="form-label">Assign Group <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select class="form-select select-picker" id="group_id" name="group_id" data-live-search="true" required>
                            <option value="">Select Group</option>
                            @foreach($ticketgroup as $group)
                                <option value="{{ $group->id }}" {{ $ticket->group_id == $group->id ? 'selected' : '' }}>
                                {{ $group->group_name }}
                            </option>

                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#groupModal">
                            Add
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Agent<span class="text-danger">*</span> </label>
                    <select name="agent_id" class="form-select" required>
                        <option value="">Select Agent</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ $ticket->agent_id == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    
                </div>

                <div class="col-md-4">
                    <label class="form-label">Project <span class="text-danger">*</span></label>
                    <select name="project_id" class="form-select" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ $ticket->project_id == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select class="form-control selectpicker" name="type_id" id="ticket_type_id" data-live-search="true" data-size="8" required>
                        <option value="">--</option>
                        @php
                            $types = ['1'=>'Bug','2'=>'Suggestion','3'=>'Question','4'=>'Sales','5'=>'Code','6'=>'Management','7'=>'Problem','8'=>'Incident','9'=>'Feature Request'];
                        @endphp
                        @foreach($types as $key => $val)
                            <option value="{{ $key }}" {{ $ticket->type_id == $key ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Ticket Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control" required value="{{ $ticket->subject }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" required>{{ $ticket->description }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Upload File</label>
                    <input type="file" name="attachment" class="form-control">
                    @if($ticket->attachment)
                        <a href="{{ asset($ticket->attachment) }}" target="_blank" class="d-block mt-1">View Current File</a>
                    @endif
                </div>

                <div class="col-md-3">
                    <label class="form-label">Priority <span class="text-danger">*</span></label>
                    <select name="priority" class="form-select" required>
                        <option value="">Select</option>
                        @foreach(['low', 'medium', 'high', 'critical'] as $p)
                            <option value="{{ $p }}" {{ $ticket->priority == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Channel</label>
                    <input type="text" name="channel" class="form-control" placeholder="e.g. Email, Chat" value="{{ $ticket->channel }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control" placeholder="Comma-separated tags" value="{{ $ticket->tags }}">
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-warning">✏️ Update Ticket</button>
                    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
        
          <!-- Assign Group Modal -->
      <div class="modal fade" id="groupModal" tabindex="-1" aria-labelledby="groupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="addGroupForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Groups</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Group List Table -->
                    <table class="table table-bordered mb-4">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Group</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody id="group-list">
                   
                            @foreach($ticketgroup as $index => $group)
                                <tr id="group-row-{{ $group->id }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $group->group_name }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger delete-group" data-id="{{ $group->id }}">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Add New Group -->
                    <div class="mb-3">
                        <label>Group Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="group_name" required placeholder="Group name">
                        <div id="group-error" class="text-danger d-none mt-2"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">💾 Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

    </div>
</div>
@endsection

@push('js')
<script>
    $('#addGroupForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route('ticket-groups.store') }}',
            method: 'POST',
            data: $(this).serialize(),
           success: function(res) {
                if (res.status === 'success') {
                    // 1. Add new group to the main select dropdown
                    $('#group_id').append(`<option value="${res.group.id}" selected>${res.group.group_name}</option>`);
                    $('.select-picker').selectpicker('refresh');
            
                    // 2. Add new group to the group list table in modal
                    const newRow = `
                        <tr id="group-row-${res.group.id}">
                            <td>#</td>
                            <td>${res.group.group_name}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger delete-group" data-id="${res.group.id}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#group-list').append(newRow);
            
                    // 3. Reset form
                    $('#addGroupForm')[0].reset();
            
                    // 4. Hide modal (Bootstrap 5)
                    const modalEl = document.getElementById('groupModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    modalInstance.hide();
                }
            },

            error: function(xhr) {
                $('#group-error').removeClass('d-none').text(xhr.responseJSON.message || 'Error occurred');
            }
        });
    });
    
    // Delete group
    $(document).on('click', '.delete-group', function () {
    var id = $(this).data('id');
    if (confirm('Are you sure you want to delete this group?')) {
        $.ajax({
            url: `{{ route('ticket-groups.destroy', '') }}/${id}`,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                if (res.status === 'success') {
                    $(`#group-row-${id}`).remove();
                    $(`#group_id option[value="${id}"]`).remove();
                    $('.select-picker').selectpicker('refresh');
                }
            }
        });
    }
});

</script>

<script>
    $(document).ready(function () {
        function toggleRequesterOptions(type) {
            $('#requester_name option').each(function () {
                const dataType = $(this).data('type');
                if (!dataType) return; // Skip default option
                $(this).toggle(dataType === type);
            });
        }

        // Initial setup based on selected type
        const selectedType = $('input[name="requester_type"]:checked').val();
        toggleRequesterOptions(selectedType);

        // On change of radio buttons
        $('input[name="requester_type"]').change(function () {
            const type = $(this).val();
            toggleRequesterOptions(type);
            $('#requester_name').val('');
        });
    });
</script>

@endpush
