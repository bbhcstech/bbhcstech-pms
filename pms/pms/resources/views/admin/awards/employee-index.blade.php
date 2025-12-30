@extends('admin.layout.app')
@section('title', 'My Awards')

@section('content')
<div class="container py-4">
    <h4 class="text-center mb-4">🏆 My Awards</h4>

    @if($awards->count())
        <div class="row">
            @foreach($awards as $award)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="fas fa-trophy fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">{{ $award->title }}</h5>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($award->award_date)->format('d M, Y') }}</small>
                                </div>
                            </div>
                            <hr>
                            <p class="card-text mt-2 text-muted">{{ $award->description }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info text-center">
            No awards received yet.
        </div>
    @endif
</div>

@endsection
