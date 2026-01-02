@extends('admin.layout.app')

@section('title', 'Create Attendance')

@section('content')

  <main class="main">
    <div class="content-wrapper py-4 px-3" style="background-color: #f5f7fa; min-height: 100vh;">
        <div class="container-fluid">
            <h4 class="fw-bold mb-4">Leaves Report</h4>

            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- All Employee --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Leave Type</label>
                    <select name="type" class="form-select">
                        <option value="">-- All Types --</option>
                        <option value="sick" {{ request('type') == 'sick' ? 'selected' : '' }}>Sick</option>
                        <option value="casual" {{ request('type') == 'casual' ? 'selected' : '' }}>Casual</option>
                        <option value="earned" {{ request('type') == 'earned' ? 'selected' : '' }}>Earned</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="col-md-2 align-self-end">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body text-center">
                            <h6>Total Leaves</h6>
                            <h4>{{ $summary['total'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body text-center">
                            <h6>Approved</h6>
                            <h4>{{ $summary['approved'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body text-center">
                            <h6>Pending</h6>
                            <h4>{{ $summary['pending'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body text-center">
                            <h6>Rejected</h6>
                            <h4>{{ $summary['rejected'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
            <table id="leavereportTable" class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr>
                            <td>{{ $leave->user?->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($leave->type) }}</td>
                            <td>{{ $leave->start_date }}</td>
                            <td>{{ $leave->end_date }}</td>
                            <td>{{ $leave->reason }}</td>
                            <td>
                                <span class="badge bg-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        No leave records found.

                    @endforelse
                </tbody>
            </table>
        </div>

        </div>
    </div>
</main>
@push('js')
<script>

     $(document).ready(function () {
    $('#leavereportTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
                search: "_INPUT_",
                searchPlaceholder: "Search leaves..."
        }
    });
  });
</script>
@endpush

@endsection
