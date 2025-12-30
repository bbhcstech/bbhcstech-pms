@extends('admin.layout.app')

@section('title', isset($parentDepartment) ? 'Edit Department' : 'Department')

@section('content')
<main class="main py-4">
    <div class="container">
        <h4>{{ isset($parentDepartment) ? 'Edit' : 'Add' }} Department</h4>

        <form method="POST"
              action="{{ isset($parentDepartment)
                        ? route('parent-departments.update', $parentDepartment->id)
                        : route('parent-departments.store') }}">

            @csrf
            @if(isset($parentDepartment)) @method('PUT') @endif

            {{-- Department Code (readonly) --}}
            <div class="mb-3">
                <label>Department Code</label>
                <input type="text"
                       class="form-control"
                       name="dpt_code"
                       value="{{ isset($parentDepartment) ? $parentDepartment->dpt_code : ($nextCode ?? 'DEP-0001') }}"
                       readonly>
            </div>

            {{-- Department Name --}}
            <div class="mb-3">
                <label>Department Name <span class="text-danger">*</span></label>
                <input type="text"
                       name="dpt_name"
                       class="form-control"
                       value="{{ old('dpt_name', $parentDepartment->dpt_name ?? '') }}"
                       required>
            </div>

            <button class="btn btn-primary">
                {{ isset($parentDepartment) ? 'Update' : 'Submit' }}
            </button>
        </form>
    </div>
</main>
@endsection
