<!-- //layout main dashboard menu ... -->

<!-- //layout/manu.blade.php -->

@php $userId = Auth::id(); @endphp

<style>
   /* ===================== GLOBAL MODAL FIX ===================== */
.modal-backdrop.show {
  opacity: 0.4 !important;
}

body.modal-open {
  opacity: 1 !important;
}

.modal { z-index: 1050; }
.modal-backdrop { z-index: 1040; }

.modal-content {
  background-color: #fff !important;
  box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.2);
  border-radius: 10px;
}

/* ===================== NAVBAR CORE FIX ===================== */
#layout-navbar {
  display: flex;
  flex-wrap: nowrap !important;
  align-items: center;
  width: 100%;
}

#layout-navbar .navbar-nav {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap !important;
  align-items: center;
}

#layout-navbar .navbar-nav > * {
  flex: 0 0 auto;
  white-space: nowrap;
}


.navbar-nav > div {
  display: contents;
}

/* ===================== RIGHT ICONS FIX ===================== */
.navbar-nav-right,
#layout-navbar .navbar-nav-right {
  display: flex;
  flex-direction: row !important;
  flex-wrap: nowrap !important;
  align-items: center;
  gap: 8px;
}

.header-icon-box {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 36px;
}

/* ===================== SEARCH FIELD CONTROL ===================== */
@media (max-width: 992px) {
  #layout-navbar input[type="text"] {
    display: none !important;
  }
}

/* ===================== MOBILE / TABLET FINAL FIX ===================== */
@media (max-width: 992px) {

  #layout-navbar {
    padding: 0.5rem 0.75rem;
  }

  #layout-navbar .navbar-nav,
  #layout-navbar .navbar-nav-right {
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center;
  }

  #layout-navbar .navbar-nav > *,
  #layout-navbar .navbar-nav-right > * {
    flex: 0 0 auto;
  }

  /* hide username only, keep avatar */
  .dropdown-user .d-md-block {
    display: none !important;
  }
}

/* ===================== EXTRA SAFETY ===================== */
.layout-menu {
  transition: transform 0.3s ease;
}

.layout-menu-active .layout-menu {
  transform: translateX(0);
}

</style>

<body>
    @php
    use App\Models\Project;
    use App\Models\Task;
    use App\Models\TaskTimer;

    $projects = Project::all();
    $tasks = Task::all();

    $activeTimer = TaskTimer::where('user_id', auth()->id())
        ->whereNull('end_time')
        ->latest()
        ->with('task.project')
        ->first();
    @endphp

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="index.html" class="app-brand-link">
              <span class="app-brand-logo demo">
                <span class="text-primary">
                  <!-- SVG omitted for brevity in editor, keep original in your file -->
                  <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> ... </svg>
                </span>
              </span>
              <span class="app-brand-text demo menu-text fw-bold ms-2">PMS</span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
            </a>
          </div>

          <div class="menu-divider mt-0"></div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">

            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <a href="{{ route('dashboard') }}" class="menu-link">
                  <i class="menu-icon tf-icons bx bx-home-smile"></i>
                  <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
              </a>
            </li>



            <!-- Layouts -->
            <li class="menu-item {{ request()->routeIs('employees.*') ||
                      request()->routeIs('designations.*') ||
                      request()->routeIs('attendance.*') ||
                      request()->routeIs('leaves.*') ||
                      request()->routeIs('holidays.*') ||
                      request()->routeIs('awards.*') ||
                      request()->routeIs('admin.leave.report') ||
                      request()->routeIs('employee.awards')
                      ? 'active open' : '' }}">

              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div class="text-truncate" data-i18n="Layouts">HR</div>
              </a>



              <ul class="menu-sub">
                    @if(auth()->user()->role === 'admin')
                        <li class="menu-item">
                            <a href="{{ route('employees.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Without menu">Employee</div>
                            </a>
                        </li>


                        <li class="menu-item">
                            <a href="{{ route('designations.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Without menu">Designation</div>
                            </a>
                        </li>

 <!-- Department with Submenu -->
                  <li class="menu-item {{ request()->routeIs('parent-departments.*') || request()->routeIs('departments.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                      <div class="text-truncate">Department</div>
                    </a>
                    <ul class="menu-sub">
                      <li class="menu-item {{ request()->routeIs('parent-departments.*') ? 'active' : '' }}">
                        <a href="{{ route('parent-departments.index') }}" class="menu-link">
                          <div class="text-truncate">Main Department</div>
                        </a>
                      </li>
                      <li class="menu-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                        <a href="{{ route('departments.index') }}" class="menu-link">
                          <div class="text-truncate">Sub Department</div>
                        </a>
                      </li>
                    </ul>
                  </li>
                @endif


                     @if(in_array(auth()->user()->role, ['admin', 'employee']))
                    <li class="menu-item {{ request()->routeIs('attendance.index') ? 'active open' : '' }}">
                          <a href="{{ route('attendance.index') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Without menu">
                              {{ auth()->user()->role == 'admin' ? 'Attendance' : 'My Attendance' }}
                            </div>
                          </a>
                    </li>
                    @endif


<!--
                @if(auth()->user()->role === 'admin')
                <li class="menu-item {{ request()->routeIs('attendance.report') ? 'active open' : '' }}">
                  <a href="{{ route('attendance.report') }}" class="menu-link">
                    <div class="text-truncate" data-i18n="Without menu">Attendance Report</div>
                  </a>
                </li>
                 @endif -->



                @if(in_array(auth()->user()->role, ['admin', 'employee']))
                <li class="menu-item {{ request()->routeIs('leaves.index') ? 'active open' : '' }}">
                <a href="{{ route('leaves.index') }}" class="menu-link">
                    <div class="text-truncate" data-i18n="Without navbar">My Leaves</div>
                </a>
                </li>
                @endif


                <!-- @if(auth()->user()->role === 'admin')
                <li class="menu-item {{ request()->routeIs('admin.leave.report') ? 'active open' : '' }}">
                <a href="{{ route('admin.leave.report') }}" class="menu-link">
                    <div class="text-truncate" data-i18n="Without navbar">Leaves Report</div>
                </a>
                </li>
                @endif -->

                {{-- Employee Holiday View --}}
                <li class="menu-item {{ request()->routeIs('holidays.calendar') ? 'active open' : '' }}">
                <a href="{{ route('holidays.calendar') }}" class="menu-link">
                    <div class="text-truncate">Holiday List</div>
                </a>
                </li>


                    @if(auth()->user()->role === 'admin')
                    <!-- Admin sees Appreciation menu -->
                    <li class="menu-item {{ request()->routeIs('awards.*') ? 'active open' : '' }}">
                        <a href="{{ route('awards.index') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Container">Recognition</div>
                        </a>
                    </li>
                    @elseif(auth()->user()->role === 'employee')
                    <!-- Employee also sees Recognition menu but goes to filtered view -->
                    <li class="menu-item {{ request()->routeIs('awards.index') ? 'active open' : '' }}">
                        <a href="{{ route('awards.index') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Container">My Awards</div>
                        </a>
                    </li>
                @endif

              </ul>
            </li>



             <!-- Reports Section -->
            <li class="menu-item {{ request()->routeIs('attendance.report') || request()->routeIs('admin.leave.report') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt"></i>
                <div class="text-truncate" data-i18n="Layouts">Reports</div>
              </a>

              <ul class="menu-sub">
                @if(auth()->user()->role === 'admin')
                  <li class="menu-item {{ request()->routeIs('attendance.report') ? 'active' : '' }}">
                    <a href="{{ route('attendance.report') }}" class="menu-link">
                      <div class="text-truncate">Attendance Report</div>
                    </a>
                  </li>

                  <li class="menu-item {{ request()->routeIs('admin.leave.report') ? 'active' : '' }}">
                    <a href="{{ route('admin.leave.report') }}" class="menu-link">
                      <div class="text-truncate">Leaves Report</div>
                    </a>
                  </li>
                @endif
              </ul>
            </li>



            <!-- Work Section -->
            <li class="menu-item {{ request()->routeIs('clients.') || request()->routeIs('projects.') ||
                request()->routeIs('tasks.') || request()->routeIs('timelogs.') ||
                request()->routeIs('admin.contracts.') || request()->routeIs('admin.contract-templates.') ? 'active open' : '' }}">

                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-store"></i>
                    <div class="text-truncate" data-i18n="Front Pages">Work</div>
                </a>

                <ul class="menu-sub">
                    @if(auth()->user()->role === 'admin')
                        <li class="menu-item {{ request()->routeIs('clients.index') ? 'active open' : '' }}">
                            <a href="{{ route('clients.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Landing">Client</div>
                            </a>
                        </li>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin', 'employee']))
                        <li class="menu-item {{ request()->routeIs('projects.index') ? 'active open' : '' }}">
                            <a href="{{ route('projects.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Landing">Projects</div>
                            </a>
                        </li>
                    @endif

                    <li class="menu-item {{ request()->routeIs('tasks.index') ? 'active open' : '' }}">
                        <a href="{{ route('tasks.index') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Pricing">Tasks</div>
                        </a>
                    </li>

                    @if(in_array(auth()->user()->role, ['admin', 'employee']))
                        <li class="menu-item">
                            <a href="{{ route('timelogs.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Payment">Timesheet</div>
                            </a>
                        </li>
                    @endif

                    <!-- Contracts Section - Admin Only -->
                     
                    @if(auth()->user()->role === 'admin')
                         <li class="menu-item {{ request()->routeIs('admin.contracts.*') ? 'active open' : '' }}">
                            <a href="{{ route('admin.contracts.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Contracts">Contracts</div>
                            </a>
                        </li>

                        <li class="menu-item {{ request()->routeIs('admin.contract-templates.*') ? 'active open' : '' }}">
                            <a href="{{ route('admin.contract-templates.index') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Contract Templates">Contract Templates</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>

<!--    //leads section -->

                   @if(auth()->user()->role === 'admin')
                    <li class="menu-item has-sub {{ request()->routeIs('leads.*') || request()->routeIs('admin.deals.*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-target-lock"></i>
                            <div class="text-truncate" data-i18n="Front Pages">Leads</div>
                        </a>

                        <ul class="menu-sub">
                            {{-- Lead Contact --}}
                            <li class="menu-item {{ request()->routeIs('leads.contacts.*') ? 'active open' : '' }}">
                                <a href="{{ route('leads.contacts.index') }}" class="menu-link">
                                    <div class="text-truncate" data-i18n="Landing">Lead Contact</div>
                                </a>
                            </li>

                            {{-- Deals --}}
                            <li class="menu-item {{ request()->routeIs('admin.deals.*') ? 'active open' : '' }}">
                                <a href="{{ route('admin.deals.index') }}" class="menu-link">
                                    <div class="text-truncate">Deals</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- //ticket section . -->


            <li class="menu-item {{ request()->routeIs('tickets.index') ? 'active' : '' }}">
                  <a href="{{ route('tickets.index') }}" class="menu-link">
                       <i class="menu-icon tf-icons bx bx-receipt"></i>
                      <div class="text-truncate" data-i18n="Dashboard">Ticket</div>
                  </a>
              </li>



                   <!-- ================== SETTINGS SECTION ==================
                <li class="menu-item {{ request()->routeIs('settings.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-cog"></i>
                        <div class="text-truncate" data-i18n="Settings">Settings</div>
                    </a>

                    <ul class="menu-sub">
                        @if(auth()->user()->role === 'admin')
                            @if(Route::has('settings.company'))
                                <li class="menu-item">
                                    <a href="{{ route('settings.company') }}" class="menu-link">
                                        <div>Company Settings</div>
                                    </a>
                                </li>
                            @endif


                           @if(Route::has('admin.settings.business-address.index'))
                            <li class="menu-item">
                                <a href="{{ route('admin.settings.business-address.index') }}" class="menu-link">
                                    <div>Business Address</div>
                                </a>
                            </li>
                        @endif


                   @if(Route::has('admin.settings.app'))
                    <li class="menu-item">
                        <a href="{{ route('admin.settings.app', ['page' => 'app']) }}" class="menu-link">
                            <div>App Settings</div>
                        </a>
                    </li>
                @endif


                        @if(Route::has('admin.settings.profile'))
                        <li class="menu-item">
                            <a href="{{ route('admin.settings.profile') }}" class="menu-link">
                                <div>Profile Settings</div>
                            </a>
                        </li>
                    @endif






                    @endif
                    </ul>
                </li>
            </ul> -->

        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav
            class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
            id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="icon-base bx bx-menu icon-md"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

              <!-- LEFT: Breadcrumbs -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item">
                  <span class="fw-bold">Dashboard</span> • Home
                </div>
              </div>

              <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                <!-- Place this tag where you want the button to render. -->

                <!-- RIGHT: Search + User -->
                <div class="navbar-nav align-items-center ms-auto d-flex">

                  <!-- Small search -->
                  <div class="nav-item d-flex align-items-center me-3" style="width: 200px;" title="Saerch">
                    <i class="bx bx-search icon-md me-2" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#searchModal"></i>
                    <input
                      type="text"
                      class="form-control border-0 shadow-none ps-1 ps-sm-2"
                      placeholder="Search..."
                      data-bs-toggle="modal"
                      data-bs-target="#searchModal"
                      readonly
                      style="cursor: pointer; height: 32px; font-size: 14px;" />
                  </div>

                  <!-- Sticky Note Icon -->
                  <div class="nav-item me-3">
                      <a href="javascript:void(0);" class="d-block header-icon-box" data-bs-toggle="modal" data-bs-target="#addNoteModal" title="Sticky Note">
                          <i class="bx bx-note icon-md text-dark"></i>
                      </a>
                  </div>

                  {{-- Header Timer Icon --}}
                  <div class="nav-item">
                      @if($activeTimer ?? false)
                          <!-- Active Timer (red icon) -->
                          <div class="nav-item me-3">
                          <a href="javascript:void(0);" class="d-block header-icon-box" data-bs-toggle="modal" data-bs-target="#activeTimerModal" title="Active Timer">
                              <i class="bx bx-time-five icon-md text-danger"></i>
                          </a>
                          </div>
                      @else
                          <!-- Start Timer -->
                          <div class="nav-item me-3">
                          <a href="javascript:void(0);" class="d-block header-icon-box" data-bs-toggle="modal" data-bs-target="#startTimerModal" title="Start Timer">
                              <i class="bx bx-time-five icon-md text-dark"></i>
                          </a>
                          </div>
                      @endif
                  </div>

                  <div class="nav-item me-3">
                      <li class="nav-item dropdown" data-bs-toggle="tooltip" data-bs-placement="top" title="Create new">
                        <a class="d-block header-icon-box" href="#" id="createNewDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-plus-circle icon-md text-dark"></i>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="createNewDropdown">
                            <li title="Add Task">
                                <a class="dropdown-item f-14 text-dark openRightModal" href="{{ route('tasks.create') }}">
                                    <i class="bx bx-plus me-2"></i> Add Task
                                </a>
                            </li>
                            <li title="Create Ticket">
                                <a class="dropdown-item f-14 text-dark openRightModal" href="{{ route('tickets.create') }}">
                                    <i class="bx bx-plus me-2"></i> Create Ticket
                                </a>
                            </li>
                        </ul>
                    </li>
                  </div>

                  <div class="nav-item me-3">
                       <li class="nav-item dropdown" title="New notifications">
                        <a class="nav-link header-icon-box" href="#" id="navbarDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell f-16 text-dark-grey"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="badge bg-danger">{{ auth()->user()->unreadNotifications->count() }}</span>
                            @endif
                        </a>

                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown border-0 shadow-lg py-0"
                                aria-labelledby="navbarDropdown" style="width: 300px;">

                                <li class="px-3 py-2 border-bottom bg-white">
                                    <p class="mb-0 fw-bold">New notifications</p>
                                </li>

                           @forelse(auth()->user()->unreadNotifications as $notification)
                            @php
                                $data = $notification->data ?? [];
                                $type = class_basename($notification->type); // e.g. TaskAssignedNotification

                                if ($taskId = data_get($data, 'task_id')) {
                                    $link = route('tasks.show', $taskId);
                                } elseif ($ticketId = data_get($data, 'ticket_id')) {
                                    $link = route('tickets.show', $ticketId);
                                } else {
                                    $link = '#';
                                }
                            @endphp

    <li class="px-3 py-2 border-bottom">
        <a href="{{ $link }}" class="text-dark d-block">
            <strong>{{ data_get($data, 'title', $type) }}</strong>
            <div class="small">{{ data_get($data, 'message', '') }}</div>
        </a>
    </li>
@empty
    <li class="px-3 py-2">
        <span class="text-muted">No new notifications</span>
    </li>
@endforelse


                                <li class="px-3 py-2 text-center">
                                    <a href="{{ route('notifications.all') }}" class="text-primary">View All</a>
                                </li>
                            </ul>
                        </li>

                  </div>

                </div>

                @php
                  use App\Models\User;
                  $user = auth()->user();
                @endphp

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a  class="nav-link dropdown-toggle hide-arrow p-0"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown">

                    <!-- Image + Name + Role -->
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-online me-2">
                        <img src="{{ $user && $user->profile_image ? asset($user->profile_image) : asset('admin/assets/img/avatars/1.png') }}"
                             alt class="w-px-40 h-auto rounded-circle" />
                      </div>

                      <div class="d-none d-md-block text-start">
                        <h6 class="mb-0 text-truncate" style="font-size: 14px;">{{ $user->name }}</h6>
                        <small class="text-muted">{{ ucfirst($user->designation ?? 'Employee' ) }}</small>
                      </div>
                    </div>
                  </a>

                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                             <img src="{{ $user && $user->profile_image ? asset($user->profile_image) : asset('admin/assets/img/avatars/1.png') }}" alt  class="w-px-40 h-auto rounded-circle" />

                            </div>
                          </div>

                          <div class="flex-grow-1">
                              <h6 class="mb-0">{{ $user->name }}</h6>
                              <small class="text-body-secondary">{{ ucfirst($user->designation ?? 'Employee' ) }} ({{ ucfirst($user->role) }})</small>
                          </div>

                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>

                      <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                               <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                            </x-dropdown-link>
                        </form>

                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>

          <!-- Search Modal -->
          <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="searchModalLabel">Search</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form action="{{ route('dashboard.search') }}" method="GET">
                      <div class="mb-3">
                          <label for="type">Search For:</label>
                          <select name="type" id="type" class="form-select">
                              <option value="ticket">Ticket</option>
                              <option value="task">Task</option>
                              <option value="project">Project</option>
                              <option value="employee">Employee</option>
                              <option value="client">Client</option>
                          </select>
                      </div>
                      <div class="mb-3">
                          <input type="text" name="query" class="form-control" placeholder="Enter keyword to search">
                      </div>
                      <button type="submit" class="btn btn-primary">Search</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Add Sticky Note Modal -->
          <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="addNoteModalLabel">Sticky Notes</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                  <!-- Add Note Form -->
                  <form action="{{ route('sticky_notes.store') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <label for="note_text" class="form-label">Note Details</label>
                        <textarea name="note_text" id="note_text" class="form-control" rows="3" placeholder="Write your note..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="colour" class="form-label">Color Code</label>
                        <select name="colour" id="colour" class="form-select" required>
                            <option value="blue">Blue</option>
                            <option value="yellow">Yellow</option>
                            <option value="red">Red</option>
                            <option value="gray">Gray</option>
                            <option value="purple">Purple</option>
                            <option value="green">Green</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end">
                      <button type="submit" class="btn btn-primary me-2">Save</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                  </form>

                  <hr>

                  <!-- Existing Notes -->
                  <h6>Your Notes</h6>
                  <div class="sticky-notes-list">
                    @php
                        $notes = \App\Models\StickyNote::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
                    @endphp

                    @if($notes->count() > 0)
                      <div class="list-group">
                        @foreach($notes as $note)
                          <div class="list-group-item mb-2" style="border-left: 5px solid {{ $note->colour }};">
                            <p class="mb-1">{{ $note->note_text }}</p>
                            <small class="text-muted">{{ $note->created_at->format('d M Y, h:i A') }}</small>
                          </div>
                        @endforeach
                      </div>
                    @else
                      <p class="text-center text-muted">- No record found -</p>
                    @endif
                  </div>

                </div>
              </div>
            </div>
          </div>

          {{-- ================= START TIMER MODAL ================= --}}
          <div class="modal fade" id="startTimerModal" tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Start Timer</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('dashboard-timers.store') }}" method="POST">
                  @csrf
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Project <sup class="text-danger">*</sup></label>
                      <select name="project_id" class="form-select" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                          <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Task <sup class="text-danger">*</sup></label>
                      <select name="task_id" id="task_id" class="form-select">
                        <option value="">Select Task</option>
                        @foreach($tasks as $task)
                          <option value="{{ $task->id }}">{{ $task->title }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" id="create_task" name="create_task" value="1">
                      <label class="form-check-label" for="create_task">Create New Task</label>
                    </div>

                    <div class="mb-3" id="newTaskDiv" style="display:none;">
                      <label class="form-label">New Task Name</label>
                      <input type="text" name="new_task_name" class="form-control">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Memo <sup class="text-danger">*</sup></label>
                      <textarea name="memo" class="form-control" rows="2" required></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Start</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          {{-- ================= ACTIVE TIMER MODAL ================= --}}
          @if($activeTimer ?? false)
          <div class="modal fade" id="activeTimerModal" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title">Active Timer</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <p><strong>Project:</strong> {{ $activeTimer->project->name ?? '' }}</p>
                  <p><strong>Task:</strong> {{ $activeTimer->task->title ?? '' }}</p>
                  <p><strong>Start:</strong> {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</p>
                  <p class="text-danger fw-bold">Running...</p>
                </div>
                <div class="modal-footer">
                  <form method="POST" action="{{ route('task-timer.pause', $activeTimer->task->id) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="timer_id" value="{{ $activeTimer->id }}">
                    <button type="submit" class="btn btn-warning">Pause</button>
                  </form>

                  <form method="POST" action="{{ route('task-timer.resume', $activeTimer->task->id) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="timer_id" value="{{ $activeTimer->id }}">
                    <button type="submit" class="btn btn-success">Resume</button>
                  </form>

                  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#stopTimerModal-{{ $activeTimer->id }}">
                    Stop
                  </button>
                </div>
              </div>
            </div>
          </div>
          @endif

          {{-- ================= STOP TIMER MODAL ================= --}}
          @if($activeTimer ?? false)
          <div class="modal fade" id="stopTimerModal-{{ $activeTimer->id }}" tabindex="-1">
            <div class="modal-dialog">
              <form method="POST" action="{{ route('task-timer.stop', $activeTimer->task->id) }}">
                @csrf
                <input type="hidden" name="timer_id" value="{{ $activeTimer->id }}">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Stop Timer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>Start:</strong> {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</p>
                    <p><strong>End:</strong> {{ now()->format('h:i A') }}</p>
                    <p><strong>Total Time:</strong>
                      {{ \Carbon\Carbon::parse($activeTimer->start_time)->diffForHumans(now(), true) }}
                    </p>
                    <div class="mb-3">
                      <label class="form-label">Memo *</label>
                      <textarea name="memo" class="form-control" rows="3" required></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          @endif

          <script>
          document.getElementById('create_task').addEventListener('change', function() {
              let newTaskDiv = document.getElementById('newTaskDiv');
              let taskSelect = document.getElementById('task_id');
              if (this.checked) {
                  newTaskDiv.style.display = 'block';   // Show new task input
                  taskSelect.disabled = true;            // Disable existing task dropdown
                  taskSelect.required = false;           // Remove required
                  newTaskDiv.querySelector('input').required = true; // Make new task input required
              } else {
                  newTaskDiv.style.display = 'none';
                  taskSelect.disabled = false;
                  taskSelect.required = true;
                  newTaskDiv.querySelector('input').required = false;
              }
          });

          document.addEventListener("DOMContentLoaded", function () {
              const elapsedSpan = document.getElementById("activeTimerElapsed");
              @if($activeTimer)
                  const startTime = new Date("{{ $activeTimer->start_time }}");
                  setInterval(() => {
                      const now = new Date();
                      const diff = Math.floor((now - startTime) / 1000);
                      const h = Math.floor(diff / 3600);
                      const m = Math.floor((diff % 3600) / 60);
                      const s = diff % 60;
                      if (elapsedSpan) elapsedSpan.innerText = ${h}h ${m}m ${s}s;
                  }, 1000);
              @endif
          });

          // Stop modal population
          document.addEventListener("DOMContentLoaded", function () {
              const stopModal = document.getElementById("stopTimerModal-{{ $activeTimer->id ?? '0' }}");
              if (stopModal) {
                  stopModal.addEventListener("show.bs.modal", function () {
                      const endTimeEl = document.getElementById("stopEndTime");
                      const totalTimeEl = document.getElementById("stopTotalTime");

                      const startTime = new Date("{{ $activeTimer->start_time ?? '' }}");
                      const now = new Date();

                      // End time
                      if (endTimeEl) endTimeEl.innerText = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                      // Total diff
                      const diff = Math.floor((now - startTime) / 1000);
                      const h = Math.floor(diff / 3600);
                      const m = Math.floor((diff % 3600) / 60);
                      const s = diff % 60;
                      if (totalTimeEl) totalTimeEl.innerText = ${h}h ${m}m ${s}s;
                  });
              }
          });

          </script>

    <!-- / Navbar -->
</body>
