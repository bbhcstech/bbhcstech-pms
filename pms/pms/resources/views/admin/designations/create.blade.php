@extends('admin.layout.app')

@section('title', isset($designation) ? 'Edit Designation' : 'Add Designation')

@section('content')
<main class="main py-4">
    <div class="container">
        <h4>{{ isset($designation) ? 'Edit' : 'Add' }} Designation</h4>

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ERROR MESSAGE --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST"
              action="{{ isset($designation) ? route('designations.update', $designation) : route('designations.store') }}">
            @csrf
            @if(isset($designation))
                @method('PUT')
            @endif

            <div class="mb-3 mt-3">
                <label>Unique Code</label>
                @if(isset($designation))
                    <input type="text" class="form-control" value="{{ $designation->unique_code }}" readonly>
                @else
                    <input type="text" class="form-control" value="Auto-generated after save" disabled>
                @endif
            </div>

            <div class="mb-3">
                <label for="name">Designation Name <span class="text-danger">*</span></label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $designation->name ?? '') }}"
                    required
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Parent designation field removed --}}

            <div class="mt-3">
                <button class="btn btn-primary">
                    {{ isset($designation) ? 'Update' : 'Submit' }}
                </button>
                <a href="{{ route('designations.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>
@endsection
