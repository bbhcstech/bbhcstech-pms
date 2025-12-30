@extends('admin.layout.app')

@section('title', 'Holiday Calendar')

@section('content')
<main class="main">
    <div class="container py-4">
        <h4 class="fw-bold mb-3">Holiday Calendar</h4>

        {{-- Filter Form --}}
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-3">
                <select name="month" class="form-select">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="year" class="form-select">
                    @foreach(range(date('Y') - 1, date('Y') + 2) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary">Filter</button>
            </div>
        </form>

        {{-- Holiday Calendar --}}
        @if($holidays->count())
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">{{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</h5>

                    @foreach($holidays as $week => $holidayGroup)
                        <div class="mb-3">
                            <h6 class="text-primary">Week {{ $week }}</h6>
                            <ul class="list-group">
                                @foreach($holidayGroup as $holiday)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ \Carbon\Carbon::parse($holiday->date)->format('D, d M Y') }}
                                        <span class="fw-semibold">{{ $holiday->title }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="alert alert-warning">No holidays found for selected month and year.</div>
        @endif
    </div>
</main>
@endsection
