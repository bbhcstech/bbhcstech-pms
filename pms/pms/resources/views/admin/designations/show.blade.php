@extends('admin.layout.app')

@section('title', 'Designations')

@section('content')

<div class="container">
    <div class="card shadow-sm rounded">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Designation Details</h5>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $designation->name }}</p>
            <p><strong>Parent Designation:</strong> {{ $designation->parent?->name ?? '-' }}</p>
            <!--<p><strong>Added By:</strong> {{ $designation->addedBy?->name ?? '-' }}</p>-->
            <!--<p><strong>Updated By:</strong> {{ $designation->updatedBy?->name ?? '-' }}</p>-->

            <div class="mt-3">
                <a href="{{ route('designations.index') }}" class="btn btn-secondary btn-sm">Back</a>
                <a href="{{ route('designations.edit', $designation->id) }}" class="btn btn-warning btn-sm">Edit</a>
            </div>
        </div>
    </div>
</div>

@endsection