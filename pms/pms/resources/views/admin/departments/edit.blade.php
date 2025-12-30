@extends('admin.layout.app')

@section('content')
<div class="container">
    <h4>Edit Department</h4>

    <form method="POST" action="{{ route('departments.update', $department->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Department Name</label>
            <input type="text" name="dpt_name" class="form-control" value="{{ old('dpt_name', $department->dpt_name) }}" required>
        </div>

        <div class="mb-3">
            <label>Parent Department (optional)</label>
            <select name="parent_dpt_id" class="form-control">
                <option value="">None</option>
                @foreach($parentDepartments as $pd)
                    <option value="{{ $pd->id }}" {{ (int)$pd->id === (int)$department->parent_dpt_id ? 'selected' : '' }}>
                        {{ $pd->dpt_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
