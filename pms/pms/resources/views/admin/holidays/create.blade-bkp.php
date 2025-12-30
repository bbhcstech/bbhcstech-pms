@extends('admin.layout.app')
@section('title', 'Add Holiday')
@section('content')
<main class="main">
    <div class="container py-4">
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

       <form method="POST" action="{{ route('holidays.store') }}">
    @csrf
    <div class="row">
        {{-- Holiday Title --}}
        <div class="col-md-6 mb-3">
            <label>Holiday Title <sup class="text-danger">*</sup></label>
            <input type="text" name="title" class="form-control" maxlength="30" required>
            <small class="form-text text-muted">Max 30 characters.</small>
        </div>

        {{-- Type --}}
        <div class="col-md-6 mb-3">
            <label>Type <sup class="text-danger">*</sup></label>
            <select name="recurring_type" class="form-control" onchange="toggleRecurringFields(this.value)" required>
                <option value="">Select Type</option>
                <option value="single">Single Date</option>
                <option value="weekly">Weekly Recurring</option>
                <option value="monthly">Monthly Recurring</option>
            </select>
        </div>

        {{-- Single Date --}}
        <div class="col-md-6 mb-3" id="dateField">
            <label>Date <sup class="text-danger">*</sup></label>
            <input type="date" name="date" class="form-control">
        </div>

        {{-- Start & End Date (for weekly/monthly) --}}
        <div class="col-md-6 mb-3" id="recurringRange" style="display: none;">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control mb-2">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control">
        </div>

        {{-- Weekly --}}
        <div class="col-md-6 mb-3" id="weekdaysField" style="display: none;">
            <label>Recurring Weekdays</label><br>
            @foreach (['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                <label class="me-3">
                    <input type="checkbox" name="weekday[]" value="{{ $day }}"> {{ $day }}
                </label>
            @endforeach
        </div>

        {{-- Monthly --}}
        <div class="col-md-6 mb-3" id="monthdatesField" style="display: none; max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
            <label>Recurring Monthly Dates</label><br>
            @for ($i = 1; $i <= 31; $i++)
                <label class="me-2">
                    <input type="checkbox" name="month_dates[]" value="{{ $i }}"> {{ $i }}
                </label>
            @endfor
        </div>
    </div>

    <button class="btn btn-success">Save</button>
    <a href="{{ route('holidays.index') }}" class="btn btn-secondary">Back</a>
</form>

    </div>
</main>

<script>
    function toggleRecurringFields(type) {
        document.getElementById('dateField').style.display = type === '' ? 'block' : 'none';
        document.getElementById('dateField').style.display = type === 'single' ? 'block' : 'none';
        document.getElementById('weekdaysField').style.display = type === 'weekly' ? 'block' : 'none';
        document.getElementById('monthdatesField').style.display = type === 'monthly' ? 'block' : 'none';
        document.getElementById('recurringRange').style.display = (type === 'weekly' || type === 'monthly') ? 'block' : 'none';
        
        if (type === 'single') {
        document.querySelector('[name=start_date]').value = '';
        document.querySelector('[name=end_date]').value = '';
    }
    }
</script>
@endsection
