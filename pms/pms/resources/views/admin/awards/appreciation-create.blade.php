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

  <form method="POST" action="{{ route('awards.appreciation-store') }}" enctype="multipart/form-data">
    @csrf

    <!-- Title -->
    <div class="row mb-3">
      <div class="col-md-6">
        <label for="title">Title <sup class="text-danger">*</sup></label>
        <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Employee of the Month" required>
      </div>
      <div class="col-md-6">
        <label for="icon">Choose Icon <sup class="text-danger">*</sup></label>
        <select class="form-control" id="icon" name="icon" required>
          <option value="">-- Select --</option>
          <option value="fa-star">⭐ Star</option>
          <option value="fa-trophy">🏆 Trophy</option>
          <option value="fa-medal">🥇 Medal</option>
        </select>
      </div>
    </div>

    <!-- Color -->
    <div class="mb-3">
      <label for="color_code">Color Code <sup class="text-danger">*</sup></label>
      <input type="color" class="form-control form-control-color" id="color_code" name="color_code" value="#FF0000" required>
    </div>

    <!-- Summary -->
    <div class="mb-3">
      <label for="summary">Summary</label>
      <textarea class="form-control" id="summary" name="summary" rows="3"></textarea>
    </div>

    <button class="btn btn-success">Save</button>
    <a href="{{ route('awards.index') }}" class="btn btn-secondary">Cancel</a>
  </form>

</div>
@endsection
