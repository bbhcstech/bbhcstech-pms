@extends('admin.layout.app')

@section('content')
<style>
.lead-detail-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.detail-section {
    margin-bottom: 30px;
}
.detail-section h5 {
    border-bottom: 2px solid #0d6efd;
    padding-bottom: 10px;
    margin-bottom: 20px;
    color: #333;
}
.detail-row {
    display: flex;
    margin-bottom: 15px;
}
.detail-label {
    font-weight: 600;
    color: #555;
    min-width: 150px;
}
.detail-value {
    color: #333;
}
</style>

<div class="container-fluid">
    <div class="mb-3">
        <h4 class="fw-semibold">Lead Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('leads.contacts.index') }}">Leads</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $lead->contact_name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="lead-detail-card p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-section">
                            <h5>Contact Information</h5>
                            <div class="detail-row">
                                <div class="detail-label">Name:</div>
                                <div class="detail-value">{{ $lead->salutation }} {{ $lead->contact_name }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Email:</div>
                                <div class="detail-value">{{ $lead->email }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Mobile:</div>
                                <div class="detail-value">{{ $lead->mobile }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Phone:</div>
                                <div class="detail-value">{{ $lead->phone }}</div>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h5>Company Information</h5>
                            <div class="detail-row">
                                <div class="detail-label">Company Name:</div>
                                <div class="detail-value">{{ $lead->company_name }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Website:</div>
                                <div class="detail-value">{{ $lead->website }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Industry:</div>
                                <div class="detail-value">{{ $lead->industry }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="detail-section">
                            <h5>Lead Details</h5>
                            <div class="detail-row">
                                <div class="detail-label">Lead Source:</div>
                                <div class="detail-value">{{ $lead->lead_source }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Status:</div>
                                <div class="detail-value">
                                    <span class="badge bg-{{ $lead->status == 'new' ? 'primary' : ($lead->status == 'client' ? 'success' : 'info') }}">
                                        {{ ucfirst($lead->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Lead Score:</div>
                                <div class="detail-value">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $lead->lead_score_color }}"
                                             role="progressbar"
                                             style="width: {{ $lead->lead_score }}%"
                                             aria-valuenow="{{ $lead->lead_score }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            {{ $lead->lead_score }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Tags:</div>
                                <div class="detail-value">
                                    @if($lead->tags)
                                        @foreach($lead->tags_array as $tag)
                                            <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h5>Assignment</h5>
                            <div class="detail-row">
                                <div class="detail-label">Lead Owner:</div>
                                <div class="detail-value">{{ $lead->owner->name ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Added By:</div>
                                <div class="detail-value">{{ $lead->creator->name ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Created:</div>
                                <div class="detail-value">{{ $lead->created_at->format('F d, Y H:i') }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Last Updated:</div>
                                <div class="detail-value">{{ $lead->updated_at->format('F d, Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($lead->address || $lead->city || $lead->state || $lead->country)
                <div class="detail-section">
                    <h5>Address Information</h5>
                    <div class="detail-value">
                        {{ $lead->address }}<br>
                        {{ $lead->city }}, {{ $lead->state }} {{ $lead->postal_code }}<br>
                        {{ $lead->country }}
                    </div>
                </div>
                @endif

                @if($lead->description)
                <div class="detail-section">
                    <h5>Description / Notes</h5>
                    <div class="detail-value">
                        {{ $lead->description }}
                    </div>
                </div>
                @endif

                @if($lead->create_deal && $lead->deal_name)
                <div class="detail-section">
                    <h5>Deal Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="detail-row">
                                <div class="detail-label">Deal Name:</div>
                                <div class="detail-value">{{ $lead->deal_name }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-row">
                                <div class="detail-label">Deal Value:</div>
                                <div class="detail-value">{{ $lead->formatted_deal_value }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-row">
                                <div class="detail-label">Deal Stage:</div>
                                <div class="detail-value">{{ $lead->deal_stage }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('leads.contacts.edit', $lead->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Lead
                    </a>
                    <a href="{{ route('leads.contacts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
