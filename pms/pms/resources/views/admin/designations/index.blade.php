@extends('admin.layout.app')

@section('title', 'Designations')

@section('content')
<main class="main py-4">
    <div class="container">

        <div class="d-flex justify-content-between mb-3">
            <h4>Designations</h4>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

            <div>
                <a href="{{ route('designations.create') }}" class="btn btn-primary mb-0">
                    Add Designation
                </a>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">

                <div class="d-flex align-items-center gap-2">
                    <select class="form-select" id="quick-action-type" style="min-width:150px;">
                        <option value="">No Action</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button class="btn btn-primary" id="quick-action-apply" disabled>Apply</button>
                </div>

                <div class="btn-group" role="group" aria-label="View Options">
                    <a href="{{ route('designations.index') }}"
                       class="btn btn-secondary f-14 btn-active"
                       data-bs-toggle="tooltip" title="Table View">
                        <i class="bi bi-list-ul"></i>
                    </a>
                    <a href="{{ route('designations.hierarchy') }}"
                       class="btn btn-secondary f-14"
                       data-bs-toggle="tooltip" title="Hierarchy">
                        <i class="bi bi-diagram-3"></i>
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table id="designationTable" class="table table-bordered table-hover table-striped align-middle">
            <thead>
            <tr>
                <th style="width:40px;">
                    <input type="checkbox" id="select-all">
                </th>
                <th>Designation Code</th>
                <th>Name</th>
                <th>Added By</th>
                <th>Last Updated By</th>
                <th style="width:120px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($designations as $designation)
                <tr>
                    <td>
                        <input type="checkbox" class="select-item" value="{{ $designation->id }}">
                    </td>
                    <td>{{ $designation->unique_code ?? '-' }}</td>
                    <td>{{ $designation->name }}</td>
                    <td>{{ $designation->addedBy?->name ?? '-' }}</td>
                    <td>{{ $designation->updatedBy?->name ?? '-' }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('designations.show', $designation->id) }}">
                                        <i class="bi bi-eye me-2"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('designations.edit', $designation->id) }}">
                                        <i class="bi bi-pencil-square me-2"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <form action="{{ route('designations.destroy', $designation->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this designation?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item text-danger" type="submit">
                                            <i class="bi bi-trash me-2"></i> Delete
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

        <form id="bulk-delete-form"
              action="{{ route('designations.bulk-delete') }}"
              method="POST"
              onsubmit="return confirmBulkDelete();"
              class="mt-2 d-flex align-items-center gap-2"
              style="display:none;">
            @csrf
            <button type="submit" id="delete-selected-btn" class="btn btn-danger" disabled>
                Delete Selected
            </button>
            <span id="bulk-info" style="color:#666;">0 selected</span>
        </form>

    </div>
</main>
@endsection


@push('js')
<script>
$(function () {

    var table = $('#designationTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Start typing to start..."
        }
    });

    var infoEl = $('#designationTable_wrapper').find('.dataTables_info');
    if (infoEl.length) infoEl.after($('#bulk-delete-form'));
    $('#bulk-delete-form').show();

    function getSelectedIds() {
        return $('.select-item:checked').map(function () {
            return $(this).val();
        }).get();
    }

    function updateUI() {
        var count = getSelectedIds().length;
        $('#bulk-info').text(count + ' selected');
        $('#delete-selected-btn').prop('disabled', count === 0);
        $('#quick-action-apply').prop('disabled', count === 0 || $('#quick-action-type').val() === '');
    }

    $(document).on('change', '.select-item', updateUI);

    $('#select-all').on('change', function () {
        $('.select-item').prop('checked', $(this).is(':checked'));
        updateUI();
    });

    $('#quick-action-type').on('change', updateUI);

    window.confirmBulkDelete = function () {
        var ids = getSelectedIds();
        if (ids.length === 0) return false;

        var form = $('#bulk-delete-form');
        form.find('input[name="ids[]"]').remove();

        ids.forEach(id => form.append(`<input type="hidden" name="ids[]" value="${id}">`));

        return confirm(`Delete ${ids.length} selected designation(s)?`);
    };

    $('#quick-action-apply').on('click', function () {
        var ids = getSelectedIds();
        if (!confirm(`Delete ${ids.length} selected designations?`)) return;

        $.post('{{ route("designations.bulk-delete") }}', {
            _token: '{{ csrf_token() }}',
            ids: ids
        }).done(() => location.reload());
    });

    table.on('draw', updateUI);
});
</script>
@endpush
