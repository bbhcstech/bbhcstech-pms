
@extends('admin.layout.app')

@section('title', 'Home Page')  

@section('content')

  <main id="main" class="main">

    

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
              <div class="content-wrapper py-4 px-3" style="background-color: #f5f7fa; min-height: 100vh;">
                <div class="container-fluid">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold mb-1">Welcome {{ $user->name }}</h4>
                            <div class="text-muted small">
                                <span class="me-3">Clock In at - 
                                    {{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : 'Not Clocked In' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold mb-1">{{ now()->format('h:i a') }}</h5>
                            <p class="mb-2 text-muted">{{ now()->format('l') }}</p>
                            
                            {{-- Dynamic Button --}}
                              @if ($attendance && $attendance->clock_in && !$attendance->clock_out)
                                {{-- Show Clock Out --}}
                                <form method="POST" action="{{ route('dashboard.clockout') }}">
                                    @csrf
                                    <button class="btn btn-danger btn-sm">Clock Out</button>
                                </form>
                            
                            @elseif(!$attendance || !$attendance->clock_in)
                                {{-- Show Clock In --}}
                                <form method="POST" action="{{ route('dashboard.clockin') }}">
                                    @csrf
                                    <button class="btn btn-success btn-sm">Clock In</button>
                                </form>
                            
                            @elseif($attendance && $attendance->clock_in && $attendance->clock_out)
                                {{-- Show Completed Message --}}
                                <span class="badge bg-success">You have completed your shift</span>
                            @endif

                        </div>
                    </div>


                    {{-- Profile and Summary Cards --}}
                    <div class="row g-3">
                        {{-- Profile Card --}}
                        <div class="col-md-4">
                            <div class="card h-100 text-center">
                            <div class="card-body">

                                {{-- Profile Image Centered --}}
                                <div class="d-flex justify-content-center mb-3">
                                    <img
                                        src="{{ $user && $user->profile_image ? asset($user->profile_image) : asset('admin/assets/img/avatars/1.png') }}"
                                        height="175"
                                        alt="User Profile"
                                        class="rounded shadow"
                                    />
                                </div>

                                {{-- Name & Designation --}}
                                <h6 class="fw-bold mb-1">{{ strtoupper($user->name) }}</h6>
                                <p class="text-muted mb-1">{{ $user->designation ?? 'Employee' }} ({{ ucfirst($user->role) }})</p>
                                <p class="text-muted small mb-2">Employee Id : {{ $user->id }}</p>

                                {{-- Stats --}}
                                <div class="d-flex justify-content-around mt-3">
                                    <div>
                                        <a href="{{ route('tasks.index') }}" class="text-decoration-none">
                                        <h6 class="mb-0">{{ $openTasksCount }}</h6> 
                                        <small class="text-muted">Open Tasks</small>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="{{ route('projects.index') }}" class="text-decoration-none">
                                         <h6 class="mb-0">{{ $projectsCount }}</h6>
                                        <small class="text-muted">Projects</small>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="{{ route('tickets.index') }}" class="text-decoration-none">
                                        <h6 class="mb-0">{{ $openTicketsCount }}</h6>
                                        <small class="text-muted">Open Tickets</small>
                                        </a>
                                    </div>
                                </div>
                                
                            </div>
                        </div>

                        </div>

                        {{-- Tasks --}}
                               <div class="col-md-4">
                                <div class="card h-100 position-relative">
                                    <div class="card-body">
                                        
                                        <a href="{{ route('tasks.index') }}" class="text-decoration-none"><h6 class="fw-bold mb-3">Tasks</h6></a>
                                        <p><span class="text-primary fw-bold">{{$pendingTasksCount}}</span> Pending</p>
                                        <p><span class="text-danger fw-bold">{{$overdueTasksCount}}</span> Overdue</p>
                                    </div>
                                    <i class="fas fa-tasks fa-2x text-secondary position-absolute" style="top: 10px; right: 10px;"></i>
                                </div>
                            </div>


                {{-- Projects --}}
                
                  <div class="col-md-4">
                        <div class="card h-100 position-relative">
                            <div class="card-body">
                                
                                <a href="{{ route('projects.index') }}" class="text-decoration-none"><h6 class="fw-bold mb-3">Projects</h6></a>
                                <p><span class="text-primary fw-bold">{{ $totalProjects}}</span> Total project</p>
                                <p><span class="text-primary fw-bold">{{$inProgressCount}}</span> In Progress</p>
                                   <p><span class="text-danger fw-bold">{{$overdueCount}}</span> Overdue</p>
                            </div>
                            <i  class="fas fa-folder-open fa-2x text-secondary position-absolute" style="top: 10px; right: 10px;"></i>
                        </div>
                    </div>

              
            </div>

            {{-- Shift Schedule & Timelogs --}}
            <div class="row g-3 my-4">
                <!--<div class="col-md-6">-->
                <!--    <div class="card">-->
                <!--        <div class="card-header"><h6 class="fw-bold mb-0">Shift Schedule</h6></div>-->
                <!--        <div class="card-body p-0">-->
                <!--            <table class="table mb-0">-->
                <!--                <thead class="table-light">-->
                <!--                    <tr>-->
                <!--                        <th>Date</th>-->
                <!--                        <th>Day</th>-->
                <!--                        <th>Shift</th>-->
                <!--                        <th>Note</th>-->
                <!--                    </tr>-->
                <!--                </thead>-->
                <!--                <tbody>-->
                <!--                    @foreach($shifts ?? [] as $shift)-->
                <!--                        <tr>-->
                <!--                            <td>{{ \Carbon\Carbon::parse($shift->date)->format('d-m-Y') }}</td>-->
                <!--                            <td>{{ \Carbon\Carbon::parse($shift->date)->format('l') }}</td>-->
                <!--                            <td><span class="badge bg-info">General Shift 1</span></td>-->
                <!--                            <td>This is default shift</td>-->
                <!--                        </tr>-->
                <!--                    @endforeach-->
                <!--                </tbody>-->
                <!--            </table>-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->

                <div class="col-md-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Week Timelogs</h6>
                            <div class="d-flex justify-content-between mb-3">
                                @foreach(['Mo','Tu','We','Th','Fr','Sa','Su'] as $day)
                                    <span class="badge bg-light text-dark">{{ $day }}</span>
                                @endforeach
                            </div>
                            <div>
                                <p class="mb-1">Duration: 0s</p>
                                <p class="mb-0">Break: 0s</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- My Tasks & Tickets --}}
            <div class="row g-3">
                <div class="col-md-6"> 
    <div class="card">
        <div class="card-header"><a href="{{ route('tasks.index') }}" class="text-decoration-none"><h6 class="fw-bold mb-0">My Tasks</h6></a></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Task#</th>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myTasks as $task)
                        <tr>
                            <td>#{{ $task->id }}</td>
                            <td>{{ $task->title }}</td>
                            <td>
                                @if($task->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($task->status == 'Doing')
                                    <span class="badge bg-primary">Doing</span>
                                @elseif($task->status == 'Incomplete')
                                    <span class="badge bg-danger">Incomplete</span>
                                @else
                                    <span class="badge bg-warning">{{ $task->status }}</span>
                                @endif
                            </td>
                            <td>{{ optional($task->due_date)->format('d-m-Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No tasks assigned</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-header"><a href="{{ route('tickets.index') }}" class="text-decoration-none"><h6 class="fw-bold mb-0">Tickets</h6></a></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ticket#</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myTickets as $ticket)
                        <tr>
                            <td>#{{ $ticket->id }}</td>
                            <td>{{ $ticket->subject }}</td>
                            <td>
                                @if($ticket->status == 'open')
                                    <span class="badge bg-warning">Open</span>
                                @elseif($ticket->status == 'resolved')
                                    <span class="badge bg-success">Resolved</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($ticket->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $ticket->created_at->format('d-m-Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No tickets found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

            </div>
            <br>
            
            <!-- Calender -->
            
            <div class="card">
            <div class="card-header"><h6 class="fw-bold mb-0">My Calendar</h6></div>
            <div class="card-body">
                <p>{{ now()->startOfWeek()->format('M d') }} – {{ now()->endOfWeek()->format('M d, Y') }}</p>
                <ul class="list-group">
                    @foreach(\Carbon\CarbonPeriod::create(now()->startOfWeek(), now()->endOfWeek()) as $day)
                        @php
                            $entry = $weeklyLogs[$day->toDateString()] ?? null;
                        @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $day->format('l, F d, Y') }}</span>
                            <span>
                                @if ($entry)
                                    <span class="badge bg-success">{{ ucfirst($entry->status) }}</span>
                                    <small class="text-muted ms-2">In: {{ $entry->clock_in ?? 'NA' }} | Out: {{ $entry->clock_out ?? 'NA' }}</small>
                                @else
                                    <span class="badge bg-secondary">No Entry</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        
        <div class="row mt-4">
    {{-- Birthdays --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="fw-bold mb-0">🎂 Birthdays Today</h6></div>
            <div class="card-body">
                @forelse($birthdaysToday as $emp)
                    <p>{{ $emp->user->name ?? 'N/A' }} ({{ \Carbon\Carbon::parse($emp->dob)->format('d M') }})</p>
                @empty
                    <p class="text-muted">No record found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Appreciations --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="fw-bold mb-0">🏆 Employee Appreciations</h6></div>
            <div class="card-body">
                @forelse($appreciations as $award)
                    <p>{{ $award->user->name ?? 'N/A' }} - {{ $award->title }}</p>
                @empty
                    <p class="text-muted">No record found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- On Leave --}}
    <div class="col-md-6 mt-3">
        <div class="card">
            <div class="card-header"><h6 class="fw-bold mb-0">🏖 On Leave Today</h6></div>
            <div class="card-body">
                @forelse($onLeaveToday as $leave)
                    <p>{{ $leave->user->name ?? 'N/A' }} ({{ $leave->type }})</p>
                @empty
                    <p class="text-muted">No record found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Joinings & Anniversaries --}}
    <div class="col-md-6 mt-3">
        <div class="card">
            <div class="card-header"><h6 class="fw-bold mb-0">🎉 Joinings & Work Anniversaries</h6></div>
            <div class="card-body">
                <p class="fw-semibold">Today's Joinings:</p>
                @forelse($todaysJoinings as $emp)
                    <p>{{ $emp->user->name ?? 'N/A' }}</p>
                @empty
                    <p class="text-muted">No record found.</p>
                @endforelse

                <hr class="my-2">

                <p class="fw-semibold">Work Anniversaries:</p>
                @forelse($workAnniversaries as $emp)
                    <p>{{ $emp->user->name ?? 'N/A' }} - {{ \Carbon\Carbon::parse($emp->joining_date)->diffInYears() }} year(s)</p>
                @empty
                    <p class="text-muted">No record found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>



                   
                </div>
          </div>
            
            <!-- / Content -->
          </div>

            </main><!-- End #main -->
  @endsection


            