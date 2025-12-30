@extends('admin.layout.app')
@section('title', 'Edit Holiday')
@section('content')
<main class="main">
    <div class="container py-4">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

       <form method="POST" action="{{ route('holidays.update', $holiday->id) }}">
    @csrf
    @method('PUT')

    {{-- Title --}}
    <div class="mb-3">
        <label>Holiday Title</label>
        <input type="text" name="title" class="form-control"
               value="{{ old('title', $holiday->title) }}" maxlength="30" required>
        <small class="form-text text-muted">Max 30 characters.</small>
    </div>

    {{-- Recurring Type --}}
    <div class="mb-3">
        <label>Type</label>
        <select name="recurring_type" class="form-control" onchange="toggleRecurringFields(this.value)">
            <option value="" {{ $recurringType === null ? 'selected' : '' }}>Single Date</option>
            <option value="weekly" {{ $recurringType === 'weekly' ? 'selected' : '' }}>Weekly Recurring</option>
            <option value="monthly" {{ $recurringType === 'monthly' ? 'selected' : '' }}>Monthly Recurring</option>
        </select>
    </div>

    {{-- Single Date --}}
    <div class="mb-3" id="dateField" style="display: {{ $recurringType === null ? 'block' : 'none' }};">
        <label>Date</label>
        <input type="date" name="date" class="form-control"
               value="{{ old('date', $holiday->date) }}">
    </div>

    {{-- Weekly --}}
    <div class="mb-3" id="weekdaysField" style="display: {{ $recurringType === 'weekly' ? 'block' : 'none' }};">
        <label>Recurring Weekdays</label><br>
        @foreach (['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
            <label class="me-3">
                <input type="checkbox" name="weekday[]" value="{{ $day }}"
                       {{ in_array($day, $weekdays ?? []) ? 'checked' : '' }}>
                {{ $day }}
            </label>
        @endforeach
    </div>

    {{-- Monthly --}}
    <div class="mb-3" id="monthdatesField" style="display: {{ $recurringType === 'monthly' ? 'block' : 'none' }};">
        <label>Recurring Monthly Dates</label><br>
        <div class="d-flex flex-wrap">
            @for ($i = 1; $i <= 31; $i++)
                <label class="me-2 mb-2">
                    <input type="checkbox" name="month_dates[]" value="{{ $i }}"
                           {{ in_array($i, $monthDates ?? []) ? 'checked' : '' }}>
                    {{ $i }}
                </label>
            @endfor
        </div>
    </div>

    {{-- Start & End Date --}}
    <div class="mb-3" id="recurringRange" style="display: {{ in_array($recurringType, ['weekly','monthly']) ? 'block' : 'none' }};">
        <label>Start Date</label>
        <input type="date" name="start_date" class="form-control mb-2"
               value="{{ old('start_date', $startDate) }}">

        <label>End Date</label>
        <input type="date" name="end_date" class="form-control"
               value="{{ old('end_date', $endDate) }}">
    </div>

    {{-- Hidden group_id (so updates apply to all holidays in the group) --}}
    <input type="hidden" name="group_id" value="{{ $holiday->group_id }}">

    <button class="btn btn-primary">Update</button>
    <a href="{{ route('holidays.index') }}" class="btn btn-secondary">Back</a>
</form>
    </div>
</main>

<script>
    function toggleRecurringFields(type) {
        document.getElementById('dateField').style.display = type === '' ? 'block' : 'none';
        document.getElementById('weekdaysField').style.display = type === 'weekly' ? 'block' : 'none';
        document.getElementById('monthdatesField').style.display = type === 'monthly' ? 'block' : 'none';
        document.getElementById('recurringRange').style.display = (type === 'weekly' || type === 'monthly') ? 'block' : 'none';
    }
    document.addEventListener('DOMContentLoaded', function () {
        const type = document.querySelector('[name=recurring_type]').value;
        toggleRecurringFields(type);
    });
</script>
@endsection
