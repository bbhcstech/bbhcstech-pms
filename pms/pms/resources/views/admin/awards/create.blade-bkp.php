@extends('admin.layout.app')
@section('title', 'Add Award')

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


    <form method="POST" action="{{ route('awards.store') }}">
        @csrf
        <div class="mb-3">
            <label>Employee <sup class="text-danger">*</sup></label>
            <select name="user_id" class="form-control" required>
                <option value="">--Select--</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Title <sup class="text-danger">*</sup></label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="mb-3" onclick="document.getElementById('award_date').focus()">
            <label for="award_date">Award Date <sup class="text-danger">*</sup></label>
            <input type="date" id="award_date" name="award_date" class="form-control" required />
        </div>

        <button class="btn btn-success">Save</button>
    </form>
</div>
@endsection
