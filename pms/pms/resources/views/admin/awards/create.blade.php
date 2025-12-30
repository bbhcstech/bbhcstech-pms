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


   <form method="POST" action="{{ route('awards.store') }}" enctype="multipart/form-data">
    @csrf

    <!-- Award, Given To, Date in one line -->
    <div class="row mb-3">
        <!-- Award -->
        <div class="col-md-4">
            <label for="award_id">Award <sup class="text-danger">*</sup></label>
            <div class="d-flex">
                <select name="award_id" id="award_id" class="form-control" required>
                    <option value="">--Select--</option>
                    @foreach($appreciations as $award)
                        <option value="{{ $award->id }}">{{ $award->title }}</option>
                    @endforeach
                </select>
              <button type="button" class="btn btn-sm btn-link ms-2" data-bs-toggle="modal" data-bs-target="#addAwardModal">+ Add</button>
            </div>
        </div>

        <!-- Given To -->
        <div class="col-md-4">
            <label for="user_id">Given To <sup class="text-danger">*</sup></label>
            <select name="user_id" id="user_id" class="form-control" required>
                <option value="">--Select--</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Date -->
        <div class="col-md-4">
            <label for="award_date">Date <sup class="text-danger">*</sup></label>
            <input type="date" id="award_date" name="award_date" class="form-control" required />
        </div>
    </div>

    <!-- Summary -->
    <div class="mb-3">
        <label for="summary">Summary</label>
        <textarea name="summary" id="summary" class="form-control" rows="4"></textarea>
    </div>

    <!-- Photo -->
    <div class="mb-3">
        <label for="photo">Photo</label>
        <input type="file" name="image" id="image" class="form-control" accept="image/*" />
    </div>

    <button class="btn btn-success">Save</button>
    <a href="{{ route('awards.index') }}" class="btn btn-secondary">Cancel</a>
</form>
<!-- Add Award Modal -->
<div class="modal fade" id="addAwardModal" tabindex="-1" aria-labelledby="addAwardLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="{{ route('awards.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addAwardLabel">Add Award</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <!-- Title -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="title">Title <sup class="text-danger">*</sup></label>
              <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Employee of the month" required>
            </div>
            <div class="col-md-6">
              <label for="icon">Choose Icon <sup class="text-danger">*</sup></label>
              <select class="form-control" id="icon" name="icon" required>
                <option value="">-- Select --</option>
                <option value="fa-star">⭐ Star</option>
                <option value="fa-trophy">🏆 Trophy</option>
                <option value="fa-medal">🥇 Medal</option>
                <!-- add more as needed -->
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
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

</div>
@endsection
