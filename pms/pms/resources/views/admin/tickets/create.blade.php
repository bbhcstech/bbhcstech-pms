@extends('admin.layout.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <span class="me-2">🎫</span>
                Create Ticket
            </h5>
            <a href="{{ route('tickets.index') }}" class="btn btn-light btn-sm">
                ← Back to List
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger m-3 mb-0">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body row g-4">

                {{-- Requester type --}}
                <div class="col-md-4">
                    <label class="form-label d-block mb-2">
                        Requester <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex align-items-center">
                        <div class="form-check me-4">
                            <input
                                type="radio"
                                class="form-check-input"
                                name="requester_type"
                                id="requester-client"
                                value="client"
                                checked
                                required
                            >
                            <label class="form-check-label" for="requester-client">
                                Client
                            </label>
                        </div>
                        <div class="form-check">
                            <input
                                type="radio"
                                class="form-check-input"
                                name="requester_type"
                                id="requester-employee"
                                value="employee"
                                required
                            >
                            <label class="form-check-label" for="requester-employee">
                                Employee
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Requester name --}}
                <div class="col-md-4">
                    <label for="requester_name" class="form-label">
                        Requester Name <span class="text-danger">*</span>
                    </label>
                    <select
                        name="requester_name"
                        id="requester_name"
                        class="form-select"
                        required
                    >
                        <option value="">Select requester</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" data-type="client">
                                {{ $client->name }}
                            </option>
                        @endforeach
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" data-type="employee" style="display: none;">
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Assign group --}}
                <!--<div class="col-md-4">-->
                <!--    <label for="group_id" class="form-label">-->
                <!--        Assign Group <span class="text-danger">*</span>-->
                <!--    </label>-->
                <!--    <div class="input-group">-->
                <!--        <select-->
                <!--            class="form-select selectpicker"-->
                <!--            id="group_id"-->
                <!--            name="group_id"-->
                <!--            data-live-search="true"-->
                <!--            required-->
                <!--        >-->
                <!--            <option value="">Select group</option>-->
                <!--            @foreach($ticketgroup as $group)-->
                <!--                <option value="{{ $group->id }}">{{ $group->group_name }}</option>-->
                <!--            @endforeach-->
                <!--        </select>-->
                <!--        <button-->
                <!--            type="button"-->
                <!--            class="btn btn-outline-secondary"-->
                <!--            data-bs-toggle="modal"-->
                <!--            data-bs-target="#groupModal"-->
                <!--        >-->
                <!--            Add-->
                <!--        </button>-->
                <!--    </div>-->
                <!--</div>-->

                {{-- Agent --}}
           <div class="col-md-4">
    <label for="agent_id" class="form-label">
        Assigned Employee <span class="text-danger">*</span>
    </label>
    <select name="agent_id" id="agent_id" class="form-select" required>
        <option value="">Select employee</option>
        @foreach($agents as $agent)
            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
        @endforeach
    </select>
</div>


                {{-- Project --}}
                <div class="col-md-4">
                    <label for="project_id" class="form-label">
                        Project <span class="text-danger">*</span>
                    </label>
                    <select name="project_id" id="project_id" class="form-select" required>
                        <option value="">Select project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Type --}}
                <div class="col-md-4">
                    <label for="ticket_type_id" class="form-label">
                        Type <span class="text-danger">*</span>
                    </label>
                    <select
                        class="form-select selectpicker"
                        name="type_id"
                        id="ticket_type_id"
                        data-live-search="true"
                        data-size="8"
                        required
                    >
                        <option value="">Select type</option>
                        <option value="1">Bug</option>
                        <option value="2">Suggestion</option>
                        <option value="3">Question</option>
                        <option value="4">Sales</option>
                        <option value="5">Code</option>
                        <option value="6">Management</option>
                        <option value="7">Problem</option>
                        <option value="8">Incident</option>
                        <option value="9">Feature Request</option>
                    </select>
                </div>

                {{-- Subject --}}
                <div class="col-md-6">
                    <label for="subject" class="form-label">
                        Ticket Subject <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="subject"
                        id="subject"
                        class="form-control"
                        placeholder="Short summary of the issue"
                        required
                    >
                </div>

                {{-- Description --}}
                <div class="col-md-6">
                    <label for="description" class="form-label">
                        Description <span class="text-danger">*</span>
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        class="form-control"
                        rows="4"
                        placeholder="Describe the issue in detail"
                        required
                    ></textarea>
                </div>

                {{-- Attachment --}}
                <div class="col-md-6">
                    <label for="attachment" class="form-label">
                        Upload File
                    </label>
                    <input
                        type="file"
                        name="attachment"
                        id="attachment"
                        class="form-control"
                    >
                    <small class="text-muted">
                        Optional. You can attach screenshots or documents.
                    </small>
                </div>

                {{-- Priority --}}
                <div class="col-md-3">
                    <label for="priority" class="form-label">
                        Priority <span class="text-danger">*</span>
                    </label>
                    <select name="priority" id="priority" class="form-select" required>
                        <option value="">Select priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>

                {{-- Channel --}}
                <!--<div class="col-md-3">-->
                <!--    <label for="channel" class="form-label">Channel</label>-->
                <!--    <input-->
                <!--        type="text"-->
                <!--        name="channel"-->
                <!--        id="channel"-->
                <!--        class="form-control"-->
                <!--        placeholder="Email, Chat, Phone"-->
                <!--    >-->
                <!--</div>-->

                {{-- Tags --}}
                <div class="col-md-6">
                    <label for="tags" class="form-label">Tags</label>
                    <input
                        type="text"
                        name="tags"
                        id="tags"
                        class="form-control"
                        placeholder="Comma separated tags"
                    >
                </div>

                {{-- Actions --}}
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">
                        💾 Save Ticket
                    </button>
                    <a href="{{ route('tickets.index') }}" class="btn btn-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </div>
        </form>

        {{-- Assign group modal --}}
        <div class="modal fade" id="groupModal" tabindex="-1" aria-labelledby="groupModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form id="addGroupForm">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="groupModalLabel">Manage Groups</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            {{-- Existing groups --}}
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px">#</th>
                                            <th>Group</th>
                                            <th style="width: 140px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="group-list">
                                        @foreach($ticketgroup as $index => $group)
                                            <tr id="group-row-{{ $group->id }}">
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $group->group_name }}</td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-danger delete-group"
                                                        data-id="{{ $group->id }}"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Add new group --}}
                            <div class="mb-2">
                                <label for="group_name" class="form-label">
                                    Group Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="group_name"
                                    name="group_name"
                                    required
                                    placeholder="Enter group name"
                                >
                                <div id="group-error" class="text-danger d-none mt-2"></div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                💾 Save
                            </button>
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
    // Create group
    $('#addGroupForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route('ticket-groups.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 'success') {

                    // Add to main dropdown
                    $('#group_id').append(
                        `<option value="${res.group.id}" selected>${res.group.group_name}</option>`
                    );
                    $('.selectpicker').selectpicker('refresh');

                    // Add to modal table
                    const newRow = `
                        <tr id="group-row-${res.group.id}">
                            <td>#</td>
                            <td>${res.group.group_name}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-danger delete-group"
                                    data-id="${res.group.id}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#group-list').append(newRow);

                    // Reset form
                    $('#addGroupForm')[0].reset();
                    $('#group-error').addClass('d-none').text('');

                    // Hide modal
                    const modalEl = document.getElementById('groupModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    modalInstance.hide();
                }
            },
            error: function (xhr) {
                $('#group-error')
                    .removeClass('d-none')
                    .text(xhr.responseJSON?.message || 'Error occurred while saving group');
            }
        });
    });

    // Delete group
    $(document).on('click', '.delete-group', function () {
        const id = $(this).data('id');

        if (!confirm('Are you sure you want to delete this group')) return;

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
                    $('.selectpicker').selectpicker('refresh');
                }
            }
        });
    });

    // Filter requester options based on type
    $(document).ready(function () {
        function toggleRequesterOptions(type) {
            $('#requester_name option').each(function () {
                const dataType = $(this).data('type');
                if (!dataType) return;
                $(this).toggle(dataType === type);
            });
            $('#requester_name').val('');
        }

        // Initial: client
        toggleRequesterOptions('client');

        $('input[name="requester_type"]').on('change', function () {
            const selectedType = $(this).val();
            toggleRequesterOptions(selectedType);
        });
    });
</script>
@endpush
