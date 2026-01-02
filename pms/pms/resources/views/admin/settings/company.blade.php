@extends('admin.layout.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <h4 class="fw-bold mb-4">Company Settings</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.company.store') }}">
        @csrf

        @if($company)
            <input type="hidden" name="id" value="{{ $company->id }}">
        @endif

        <div class="row">
            <!-- Company Name -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                <input type="text" name="company_name" class="form-control"
                    value="{{ old('company_name', $company->company_name ?? '') }}">
                @error('company_name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Company Email -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Company Email <span class="text-danger">*</span></label>
                <input type="email" name="company_email" class="form-control"
                    value="{{ old('company_email', $company->company_email ?? '') }}">
                @error('company_email')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Company Phone -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Company Phone <span class="text-danger">*</span></label>
                <input type="text" name="company_phone" class="form-control"
                    value="{{ old('company_phone', $company->company_phone ?? '') }}">
                @error('company_phone')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Company Website -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Company Website</label>
                <input type="text" name="company_website" class="form-control"
                    value="{{ old('company_website', $company->company_website ?? '') }}">
                @error('company_website')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-info">
            <i class="bx bx-save"></i> {{ $company ? 'Update' : 'Save' }}
        </button>

        @if($company)
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class="bx bx-trash"></i> Delete
        </button>
        @endif
    </form>

    <!-- Delete Modal -->
    @if($company)
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
           <form method="POST" action="{{ route('settings.company.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this company info?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
