@extends('admin.layout.app')

@section('title', 'Subcategory Listing')

@section('content')

<main id="main" class="main">

  <div class="pagetitle">
    <h1>Subcategory Listing</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Subcategory</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <div class="mb-3">
    <a href="{{ route('subcategory.create') }}" class="btn btn-primary">Add New Subcategory</a>
  </div>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="table-responsive">
    <table id="example1" class="table table-striped table-bordered" style="font-size:13px;">
      <thead class="thead-dark">
        <tr>
          <th>#</th>
          <th>Subcategory Name</th>
          <th>Category</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($subcategories as $index => $subcategory)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $subcategory->name }}</td>
          <td>{{ $subcategory->category->name }}</td>
          <td>
            <a href="{{ route('subcategory.edit', $subcategory->id) }}" class="btn btn-warning btn-sm">Edit</a>
            <form action="{{ route('subcategory.destroy', $subcategory->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this subcategory?')">Delete</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

</main>
@endsection



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
