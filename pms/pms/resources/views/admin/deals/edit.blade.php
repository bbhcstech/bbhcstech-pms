@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Edit Deal: {{ $deal->deal_name }}</h4>
                        <a href="{{ route('admin.deals.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Deals
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.deals.update', $deal) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <!-- Deal Information -->
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Deal Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="deal_name" class="form-label">Deal Name *</label>
                                            <input type="text" class="form-control" id="deal_name" name="deal_name"
                                                   value="{{ $deal->deal_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="value" class="form-label">Value *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" class="form-control" id="value"
                                                       name="value" value="{{ $deal->value }}" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="close_date" class="form-label">Close Date *</label>
                                            <input type="date" class="form-control" id="close_date" name="close_date"
                                                   value="{{ $deal->close_date->format('Y-m-d') }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="next_follow_up" class="form-label">Next Follow Up</label>
                                            <input type="date" class="form-control" id="next_follow_up" name="next_follow_up"
                                                   value="{{ $deal->next_follow_up ? $deal->next_follow_up->format('Y-m-d') : '' }}">
                                        </div>
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
                                        <div class="mb-3">
                                            <label for="lead_name" class="form-label">Lead Name *</label>
                                            <input type="text" class="form-control" id="lead_name" name="lead_name"
                                                   value="{{ $deal->lead_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_details" class="form-label">Contact Details *</label>
                                            <input type="text" class="form-control" id="contact_details" name="contact_details"
                                                   value="{{ $deal->contact_details }}" required>
                                        </div>
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
                                        <div class="mb-3">
                                            <label for="deal_stage_id" class="form-label">Stage *</label>
                                            <select class="form-select" id="deal_stage_id" name="deal_stage_id" required>
                                                @foreach($stages as $stage)
                                                    <option value="{{ $stage->id }}"
                                                            data-color="{{ $stage->color }}"
                                                            {{ $deal->deal_stage_id == $stage->id ? 'selected' : '' }}>
                                                        {{ $stage->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="deal_category_id" class="form-label">Category</label>
                                            <select class="form-select" id="deal_category_id" name="deal_category_id">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                            {{ $deal->deal_category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="deal_agent_id" class="form-label">Deal Agent</label>
                                            <select class="form-select" id="deal_agent_id" name="deal_agent_id">
                                                <option value="">Select Agent</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}"
                                                            {{ $deal->deal_agent_id == $agent->id ? 'selected' : '' }}>
                                                        {{ $agent->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Information -->
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Product & Pipeline</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="pipeline" class="form-label">Pipeline</label>
                                            <select class="form-select" id="pipeline" name="pipeline">
                                                <option value="Sales Pipeline" {{ $deal->pipeline == 'Sales Pipeline' ? 'selected' : '' }}>
                                                    Sales Pipeline
                                                </option>
                                                <option value="Marketing Pipeline" {{ $deal->pipeline == 'Marketing Pipeline' ? 'selected' : '' }}>
                                                    Marketing Pipeline
                                                </option>
                                                <option value="Other Pipeline" {{ $deal->pipeline == 'Other Pipeline' ? 'selected' : '' }}>
                                                    Other Pipeline
                                                </option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="product" class="form-label">Product</label>
                                            <select class="form-select" id="product" name="product">
                                                <option value="">Select Product</option>
                                                <option value="Project Management Software"
                                                        {{ $deal->product == 'Project Management Software' ? 'selected' : '' }}>
                                                    Project Management Software
                                                </option>
                                                <option value="Custom Website Development"
                                                        {{ $deal->product == 'Custom Website Development' ? 'selected' : '' }}>
                                                    Custom Website Development
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Additional Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="4">{{ $deal->notes }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="col-md-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.deals.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Deal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Stage color preview
        const stageSelect = document.getElementById('deal_stage_id');
        stageSelect.addEventListener('change', function() {
            const selectedOption = stageSelect.options[stageSelect.selectedIndex];
            const color = selectedOption.getAttribute('data-color');
            if (color) {
                stageSelect.style.borderColor = color;
            }
        });

        // Trigger change on load to set initial color
        stageSelect.dispatchEvent(new Event('change'));
    });
</script>
@endpush
