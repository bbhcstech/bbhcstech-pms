@extends('admin.layout.app')
@section('title', 'Awards List')

@section('content')
<div class="container py-4">

   
    <form method="GET" action="{{ route('awards.apreciation-index') }}" class="mb-3 d-flex gap-2">
            <select name="status" class="form-select w-auto">
                <option value="">-- All Status --</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        &nbsp;
   
  <div class="d-flex align-items-center mb-3
    @if(auth()->user()->role === 'admin') justify-content-between @else justify-content-end @endif">

    <!-- Left side: Add Award button (only for admin) -->
    @if(auth()->user()->role === 'admin')
        <div>
            <a href="{{ route('awards.apreciation-create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> &nbsp; Add Award
            </a>
        </div>
    @endif
    
    
        @if(auth()->user()->role === 'admin')
    <div class="d-flex align-items-center mb-3 justify-content-end">
        <div class="btn-group align-items-center" role="group">
            <!-- Main bulk action -->
            <select id="bulkAction" class="form-select form-select-sm" disabled>
                <option value="">No Action</option>
                <option value="status">Change Status</option>
                <option value="delete">Delete</option>
            </select>
    
            <!-- Hidden status select -->
            <select id="statusSelect" class="form-select form-select-sm ms-2" style="display:none;">
                <option value="">Select Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
    
            <!-- Apply button -->
            <button id="applyAction" class="btn btn-primary btn-sm ms-2" disabled>Apply</button>
        </div>
    
    @endif
&nbsp;

    <!-- Right side: Icon-based buttons -->
    <div class="btn-group" role="group">
        <a href="{{ route('awards.index') }}" 
           class="btn btn-secondary f-14 btn-active" 
           data-toggle="tooltip" data-original-title="Appreciation">
            <i class="bi bi-trophy"></i>
        </a>
        <a href="{{ route('awards.apreciation-index') }}" 
           class="btn btn-secondary f-14" 
           data-toggle="tooltip" data-original-title="Award">
            <i class="bi bi-award"></i>
        </a>
    </div>
</div>

</div>

  @if(session('success'))
    <div class="alert alert-success" style="background-color: #28a745; color: white; border-color: #28a745;">
        {{ session('success') }}
    </div>
@endif





<div class="table-responsive mt-3">
    <table class="table table-bordered table-hover table-striped align-middle w-100" id="awardTable">
        <thead>
            <tr>
                <th style="width:40px;">
                    <input type="checkbox" id="selectAll">
                </th>
                <th>Given To</th>
                <th>Award Name</th>
                <th>Given On</th>
                @if(auth()->user()->role === 'admin')
                    <th>Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($appreciations as $appreciation)
                <tr>
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $appreciation->id }}">
                    </td>
                    <td>{{ $appreciation->given_to ?? '-' }}</td>
                    <td>{{ $appreciation->title }}</td>
                    <td>{{ $appreciation->given_on ? \Carbon\Carbon::parse($appreciation->given_on)->format('d M Y') : '-' }}</td>
                    
                    @if(auth()->user()->role === 'admin')
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" type="button" 
                                        id="dropdownMenuButton{{ $appreciation->id }}" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $appreciation->id }}">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('awards.appreciation-edit', $appreciation->id) }}">
                                            <i class="bi bi-pencil-square me-2"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('awards.appreciation-destroy', $appreciation->id) }}" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this appreciation?');">
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
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No data available in table</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>



</div>
@push('js')
<script>

$(document).on('change', '.change-appreciation-status', function () {
    let appreciationId = $(this).data('appreciation-id');
    let newStatus = $(this).val();

    $.ajax({
        url: "{{ route('appreciations.updateStatus', ':id') }}".replace(':id', appreciationId),
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            status: newStatus
        },
        success: function (response) {
            if (response.success) {
                alert("Status updated successfully!");
            } else {
                alert("Something went wrong");
            }
        },
        error: function () {
            alert("Failed to update status");
        }
    });
});


$(document).ready(function () {
    // Select/Deselect all
    $('#selectAll').on('click', function () {
        $('.row-checkbox').prop('checked', this.checked).trigger('change');
    });

    // Enable/Disable bulk action
    $(document).on('change', '.row-checkbox', function () {
        var selected = $('.row-checkbox:checked').length;
        $('#bulkAction, #applyAction').prop('disabled', selected === 0);
        if (selected === 0) {
            $('#statusSelect').hide().val('');
        }
    });

    // Show status dropdown if "Change Status" is chosen
    $('#bulkAction').on('change', function () {
        if ($(this).val() === 'status') {
            $('#statusSelect').show();
        } else {
            $('#statusSelect').hide().val('');
        }
    });

    // Apply action
    $('#applyAction').on('click', function () {
        var action = $('#bulkAction').val();
        var status = $('#statusSelect').val();
        var ids = $('.row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (!action || ids.length === 0) {
            alert("Please select at least one item and an action.");
            return;
        }
        if (action === 'status' && !status) {
            alert("Please select Active/Inactive.");
            return;
        }
        if (action === 'delete' && !confirm("Delete selected appreciations?")) {
            return;
        }

        $.ajax({
            url: "{{ route('apreciation.bulkAction') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                action: action,
                status: status,
                ids: ids
            },
            success: function () {
                $('#bulkAction').val('');
                $('#statusSelect').hide().val('');
                $('#applyAction').prop('disabled', true);
                $('.row-checkbox, #selectAll').prop('checked', false);
                location.reload();
            },
            error: function () {
                alert("Something went wrong!");
            }
        });
    });
});


</script>


@endpush

@endsection

