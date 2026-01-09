@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Deals - Kanban View</h4>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="toggleView()">
                                <i class="fas fa-exchange-alt"></i> Table View
                            </button>
                            <a href="{{ route('admin.deals.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Deal
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters Section (same as index view - you can include it) -->
                    <form method="GET" action="{{ route('admin.deals.index') }}" id="filterForm">
                        <input type="hidden" name="view" value="kanban">
                        <!-- Add your filters here same as index page -->
                    </form>

                    <!-- Kanban Board -->
                    <div class="kanban-board">
                        <div class="row">
                            @foreach($stages as $stage)
                            <div class="col-md">
                                <div class="card">
                                    <div class="card-header" style="background-color: {{ $stage->color }}; color: white;">
                                        <h6 class="mb-0">{{ $stage->name }}</h6>
                                        <small>{{ $dealsByStage[$stage->id]->count() }} deals</small>
                                    </div>
                                    <div class="card-body kanban-column" data-stage-id="{{ $stage->id }}">
                                        @foreach($dealsByStage[$stage->id] as $deal)
                                        <div class="card mb-2 deal-card" data-deal-id="{{ $deal->id }}">
                                            <div class="card-body p-2">
                                                <h6 class="card-title mb-1">{{ $deal->deal_name }}</h6>
                                                <p class="card-text mb-1 small">
                                                    <strong>Lead:</strong> {{ $deal->lead_name }}
                                                </p>
                                                <p class="card-text mb-1 small">
                                                    <strong>Value:</strong> ₹{{ number_format($deal->value, 2) }}
                                                </p>
                                                <p class="card-text mb-1 small">
                                                    <strong>Close:</strong> {{ $deal->close_date->format('d M') }}
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <span class="badge bg-secondary">{{ $deal->product }}</span>
                                                    <div class="btn-group">
                                                        <a href="{{ route('admin.deals.edit', $deal) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleView() {
        window.location.href = "{{ route('admin.deals.index') }}?view=table";
    }

    // Drag and drop functionality (basic implementation)
    document.addEventListener('DOMContentLoaded', function() {
        const columns = document.querySelectorAll('.kanban-column');

        columns.forEach(column => {
            column.addEventListener('dragover', function(e) {
                e.preventDefault();
            });

            column.addEventListener('drop', function(e) {
                e.preventDefault();
                const dealId = e.dataTransfer.getData('text/plain');
                const stageId = this.getAttribute('data-stage-id');

                // Update deal stage via AJAX
                updateDealStage(dealId, stageId);
            });
        });

        const dealCards = document.querySelectorAll('.deal-card');
        dealCards.forEach(card => {
            card.setAttribute('draggable', 'true');

            card.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('text/plain', this.getAttribute('data-deal-id'));
            });
        });
    });

    function updateDealStage(dealId, stageId) {
        fetch(`/deals/${dealId}/update-stage`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ stage_id: stageId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
@endpush
@endsection
