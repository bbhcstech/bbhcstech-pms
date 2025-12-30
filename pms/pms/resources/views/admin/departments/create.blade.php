@extends('admin.layout.app')

@section('content')
<div class="container">
    <h4>Add Department</h4>

    <form method="POST" action="{{ route('departments.store') }}">
        @csrf

        {{-- Auto-generated Code --}}
        <div class="mb-3">
            <label>Department Code (Auto Generated)</label>
            <input type="text" class="form-control" value="{{ $nextCode }}" readonly>
        </div>

        {{-- Department Name --}}
        <div class="mb-3">
            <label>Department Name <span class="text-danger">*</span></label>
            <input type="text" name="dpt_name" class="form-control" required>
            @error('dpt_name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Parent Department REQUIRED --}}
        <div class="mb-3">
            <label>Parent Department <span class="text-danger">*</span></label>
            <select name="parent_dpt_id" class="form-control" required>
                <option value="">Select Parent Department</option>
                @foreach($parentDepartments as $pd)
                    <option value="{{ $pd->id }}">{{ $pd->dpt_name }}</option>
                @endforeach
            </select>
            @error('parent_dpt_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
