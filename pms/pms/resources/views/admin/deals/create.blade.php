@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Create New Deal</h4>
                        <a href="{{ route('admin.deals.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Deals
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.deals.store') }}" method="POST">
                        @csrf
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
                                                   placeholder="Enter deal name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="value" class="form-label">Value *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" class="form-control" id="value"
                                                       name="value" placeholder="0.00" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="close_date" class="form-label">Close Date *</label>
                                            <input type="date" class="form-control" id="close_date" name="close_date" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="next_follow_up" class="form-label">Next Follow Up</label>
                                            <input type="date" class="form-control" id="next_follow_up" name="next_follow_up">
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
                                                   placeholder="Enter lead name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_details" class="form-label">Contact Details *</label>
                                            <input type="text" class="form-control" id="contact_details" name="contact_details"
                                                   placeholder="Email or phone number" required>
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
                                                <option value="">Select Stage</option>
                                                @foreach($stages as $stage)
                                                    <option value="{{ $stage->id }}" data-color="{{ $stage->color }}">
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
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="deal_agent_id" class="form-label">Deal Agent</label>
                                            <select class="form-select" id="deal_agent_id" name="deal_agent_id">
                                                <option value="">Select Agent</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
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
                                                <option value="Sales Pipeline">Sales Pipeline</option>
                                                <option value="Marketing Pipeline">Marketing Pipeline</option>
                                                <option value="Other Pipeline">Other Pipeline</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="product" class="form-label">Product</label>
                                            <select class="form-select" id="product" name="product">
                                                <option value="">Select Product</option>
                                                <option value="Project Management Software">Project Management Software</option>
                                                <option value="Custom Website Development">Custom Website Development</option>
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
                                            <textarea class="form-control" id="notes" name="notes" rows="4"
                                                      placeholder="Enter any additional notes..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="col-md-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create Deal
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
        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        const nextWeek = new Date();
        nextWeek.setDate(nextWeek.getDate() + 7);
        const nextWeekFormatted = nextWeek.toISOString().split('T')[0];

        document.getElementById('close_date').value = today;
        document.getElementById('next_follow_up').value = nextWeekFormatted;

        // Stage color preview
        const stageSelect = document.getElementById('deal_stage_id');
        stageSelect.addEventListener('change', function() {
            const selectedOption = stageSelect.options[stageSelect.selectedIndex];
            const color = selectedOption.getAttribute('data-color');
            if (color) {
                stageSelect.style.borderColor = color;
            }
        });
    });
</script>
@endpush
