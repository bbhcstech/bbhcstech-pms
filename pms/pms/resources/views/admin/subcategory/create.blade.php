@extends('admin.layout.app')

@section('title', 'Create Subcategory')

@section('content')

<main id="main" class="main">

  <div class="pagetitle">
    <h1>Create Subcategory</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('subcategory.index') }}">Subcategory</a></li>
        <li class="breadcrumb-item active">Create</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

 <div class="card" style="background-color: #d8e0f1; padding: 20px; border-radius: 10px; ">
    <div class="card-body">
      <h5 class="card-title">Add a New Subcategory</h5>
      <form action="{{ route('subcategory.store') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label for="category_id" class="form-label">Category</label>
          <select class="form-select" id="category_id" name="category_id" required>
            <option value="">Select a category</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label for="name" class="form-label">Subcategory Name</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Enter subcategory name" required>
        </div>
         </div>
  </div>
<div class="text-end">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('subcategory.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
   
</main>
@endsection
