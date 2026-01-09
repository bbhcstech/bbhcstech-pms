@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Deal Details: {{ $deal->deal_name }}</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.deals.edit', $deal) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.deals.destroy', $deal) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                            <a href="{{ route('admin.deals.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Deals
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Deal Information -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Deal Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">Deal Name:</th>
                                            <td>{{ $deal->deal_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Value:</th>
                                            <td><strong>₹{{ number_format($deal->value, 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Close Date:</th>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $deal->close_date->format('d M Y') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Next Follow Up:</th>
                                            <td>
                                                @if($deal->next_follow_up)
                                                    <span class="badge bg-warning">
                                                        {{ $deal->next_follow_up->format('d M Y') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Not Set</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Lead Information -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Lead Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">Lead Name:</th>
                                            <td>{{ $deal->lead_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Contact Details:</th>
                                            <td>{{ $deal->contact_details }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Deal Configuration -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Deal Configuration</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">Stage:</th>
                                            <td>
                                                <span class="badge" style="background-color: {{ $deal->stage->color }}">
                                                    {{ $deal->stage->name }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Category:</th>
                                            <td>
                                                @if($deal->category)
                                                    <span class="badge bg-primary">{{ $deal->category->name }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Not Set</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Deal Agent:</th>
                                            <td>
                                                @if($deal->agent)
                                                    <span class="badge bg-success">{{ $deal->agent->name }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Not Assigned</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Product & Pipeline -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Product & Pipeline</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">Pipeline:</th>
                                            <td>
                                                <span class="badge bg-info">{{ $deal->pipeline }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Product:</th>
                                            <td>
                                                @if($deal->product)
                                                    <span class="badge bg-primary">{{ $deal->product }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Not Set</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Additional Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Notes:</label>
                                        <div class="border rounded p-3 bg-light">
                                            {{ $deal->notes ? nl2br(e($deal->notes)) : 'No notes available.' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="col-md-12">
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">System Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">Created At:</small>
                                            <p>{{ $deal->created_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Last Updated:</small>
                                            <p>{{ $deal->updated_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Status:</small>
                                            <p>
                                                @if($deal->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
