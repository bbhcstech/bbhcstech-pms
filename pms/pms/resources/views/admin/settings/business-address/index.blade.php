@extends('admin.layout.app')

@section('content')
<div class="container-xxl">
    <h4 class="mb-4">Business Addresses</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Address List</h5>
                <a href="{{ route('admin.settings.business-address.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Address
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($addresses && $addresses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Location</th>
                                <th>Address</th>
                                <th>Country</th>
                                <th>Tax Name</th>
                                <th>Default</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($addresses as $index => $address)
                            <tr class="{{ $address->is_default ? 'table-success' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $address->location }}</td>
                                <td>{{ Str::limit($address->address, 50) }}</td>
                                <td>{{ $address->country }}</td>
                                <td>{{ $address->tax_name ?? 'N/A' }}</td>
                                <td>
                         @if($address->is_default)
                        <span class="badge bg-success">Default</span>
                    @else
                        <form action="{{ route('admin.settings.business-address.make-default', $address->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <button type="submit" class="btn btn-warning btn-sm">
                                Make Default
                            </button>
                        </form>
                    @endif

                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.settings.business-address.edit', $address) }}"
                                           class="btn btn-warning">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.settings.business-address.destroy', $address) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this address?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-building display-1 text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Business Addresses Found</h5>
                    <p class="text-muted mb-4">Add your first business address to get started.</p>
                    <a href="{{ route('admin.settings.business-address.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add First Address
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
