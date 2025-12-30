@extends('admin.layout.app')

@section('title', 'Edit Subcategory')

@section('content')

<main id="main" class="main">

  <div class="pagetitle">
    <h1>Edit Subcategory</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('subcategory.index') }}">Subcategory</a></li>
        <li class="breadcrumb-item active">Edit</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

 <div class="card" style="background-color: #d8e0f1; padding: 20px; border-radius: 10px; ">
    <div class="card-body">
      <h5 class="card-title">Edit Subcategory</h5>
      <form action="{{ route('subcategory.update', $subcategory->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label for="category_id" class="form-label">Category</label>
          <select class="form-select" id="category_id" name="category_id" required>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" {{ $subcategory->category_id == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label for="name" class="form-label">Subcategory Name</label>
          <input type="text" class="form-control" id="name" name="name" value="{{ $subcategory->name }}" required>
        </div>
        </div>
  </div>
  <div class="text-end">
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('subcategory.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    

</main>
@endsection
