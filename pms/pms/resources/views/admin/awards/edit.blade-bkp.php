@extends('admin.layout.app')
@section('title', 'Edit Award')

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

    <form method="POST" action="{{ route('awards.update', $award->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Employee</label>
            <select name="user_id" class="form-control" disabled>
                <option value="">--Select--</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $award->user_id == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Title <sup class="text-danger">*</sup></label>
            <input type="text" name="title" class="form-control" value="{{ $award->title }}" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control">{{ $award->description }}</textarea>
        </div>

        <div class="mb-3">
            <label>Award Date</label>
            <input type="date" name="award_date" class="form-control" value="{{ \Carbon\Carbon::parse($award->award_date)->format('Y-m-d') }}" disabled>
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
