@extends('admin.layout.app')
@section('title', 'Add Appreciation')

@section('content')
<div class="container py-4">

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- FORM 1: CREATE AWARD -->
    <form method="POST" action="{{ route('awards.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Award, Given To, Date in one line -->
        <div class="row mb-3">
            <!-- Award (Appreciation Template) -->
            <div class="col-md-4">
                <label for="appreciation_id">Appreciation Template <sup class="text-danger">*</sup></label>
                <div class="d-flex">
                    <select name="appreciation_id" id="appreciation_id" class="form-control" required>
                        <option value="">--Select--</option>
                        @foreach($appreciations as $appreciation)
                            <option value="{{ $appreciation->id }}" {{ old('appreciation_id') == $appreciation->id ? 'selected' : '' }}>
                                {{ $appreciation->title }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-sm btn-link ms-2" data-bs-toggle="modal" data-bs-target="#addAppreciationModal">+ Add</button>
                </div>
                @error('appreciation_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Given To -->
            <div class="col-md-4">
                <label for="user_id">Given To <sup class="text-danger">*</sup></label>
                <select name="user_id" id="user_id" class="form-control" required>
                    <option value="">--Select--</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('user_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Date -->
            <div class="col-md-4">
                <label for="award_date">Date <sup class="text-danger">*</sup></label>
                <input type="date" id="award_date" name="award_date" class="form-control" value="{{ old('award_date') }}" required />
                @error('award_date')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Summary -->
        <div class="mb-3">
            <label for="summary">Summary</label>
            <textarea name="summary" id="summary" class="form-control" rows="4">{{ old('summary') }}</textarea>
        </div>

        <!-- Photo -->
        <div class="mb-3">
            <label for="photo">Photo</label>
            <input type="file" name="photo" id="photo" class="form-control" accept="image/*" />
            @error('photo')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-success">Save Award</button>
        <a href="{{ route('awards.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

    <!-- MODAL: CREATE APPRECIATION TEMPLATE -->
    <div class="modal fade" id="addAppreciationModal" tabindex="-1" aria-labelledby="addAppreciationLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('awards.appreciation-store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAppreciationLabel">Add Appreciation Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Title -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modal_title">Title <sup class="text-danger">*</sup></label>
                                <input type="text" class="form-control" id="modal_title" name="title" placeholder="e.g. Employee of the month" required>
                            </div>
                            <div class="col-md-6">
                                <label for="status">Status <sup class="text-danger">*</sup></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="">-- Select --</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Color -->
                        <div class="mb-3">
                            <label for="color_code">Color Code <sup class="text-danger">*</sup></label>
                            <input type="color" class="form-control form-control-color" id="color_code" name="color_code" value="#FF0000" required>
                        </div>

                        <!-- Given To (Optional) -->
                        <div class="mb-3">
                            <label for="given_to">Given To (Optional)</label>
                            <input type="text" class="form-control" id="given_to" name="given_to" placeholder="e.g. All Employees, Sales Team">
                        </div>

                        <!-- Given On (Optional) -->
                        <div class="mb-3">
                            <label for="given_on">Given On (Optional)</label>
                            <input type="date" class="form-control" id="given_on" name="given_on">
                        </div>

                        <!-- Summary -->
                        <div class="mb-3">
                            <label for="modal_summary">Summary (Optional)</label>
                            <textarea class="form-control" id="modal_summary" name="summary" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // When appreciation template is added via modal, refresh the dropdown
    const appreciationModal = document.getElementById('addAppreciationModal');

    appreciationModal.addEventListener('hidden.bs.modal', function (event) {
        // Refresh page to show newly added appreciation in dropdown
        location.reload();
    });
});
</script>
@endpush

@endsection
