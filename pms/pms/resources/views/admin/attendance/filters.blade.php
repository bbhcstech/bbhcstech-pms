<form id="attendanceFilter" class="d-flex align-items-center gap-2">
    <select name="month" id="month">
        @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
            </option>
        @endforeach
    </select>


    <select name="year" class="form-select form-select-sm" id="year" style="width: auto;">
        @foreach(range(date('Y') - 2, date('Y') + 2) as $y)
            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
</form>
