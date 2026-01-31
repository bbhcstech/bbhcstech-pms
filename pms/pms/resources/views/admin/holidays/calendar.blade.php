@extends('admin.layout.app')

@section('title', 'Holiday Calendar')

@section('content')
<main class="main">
    <div class="container py-4">
        @php
            $isAdmin = auth()->user()->role === 'admin';
        @endphp

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Holiday Calendar</h4>
                @if(!$isAdmin)
                    <p class="text-muted mb-0 small">View only - All holidays are managed by Admin</p>
                @endif
            </div>

            <!-- Action Buttons (Only for Admin) -->
            @if($isAdmin)
            <div>
                <a href="{{ route('holidays.create') }}" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle me-1"></i> Add Holiday
                </a>
                <a href="{{ route('holidays.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul me-1"></i> List View
                </a>
            </div>
            @else
            <!-- Employee এর জন্য শুধু View Switcher -->
            <div>
                <a href="{{ route('holidays.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul me-1"></i> List View
                </a>
            </div>
            @endif
        </div>

        <!-- Calendar -->
        <div class="card">
            <div class="card-body">
                <div id='calendar'></div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-3">
            <h6 class="fw-bold mb-2">Legend:</h6>
            <div class="d-flex flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    <div class="color-box me-2" style="width: 20px; height: 20px; background-color: #0d6efd;"></div>
                    <small>Regular Holiday</small>
                </div>
                <div class="d-flex align-items-center">
                    <div class="color-box me-2" style="width: 20px; height: 20px; background-color: #28a745;"></div>
                    <small>Weekly Holiday</small>
                </div>
                @if(!$isAdmin)
                <div class="d-flex align-items-center ms-4">
                    <i class="bi bi-eye me-2 text-muted"></i>
                    <small class="text-muted">Read-only view</small>
                </div>
                @endif
            </div>
        </div>

        <!-- Holiday Details (Shows when clicked) -->
        <div class="card mt-4 d-none" id="holidayDetailsCard">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Holiday Details</h5>
            </div>
            <div class="card-body" id="holidayDetailsContent">
                <!-- Details will load here -->
            </div>
        </div>
    </div>
</main>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
    <style>
        .fc-event {
            cursor: pointer;
            border-radius: 4px;
        }
        .color-box {
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }
        .holiday-detail {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #0d6efd;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 650,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                events: {!! $holidays !!},
                eventClick: function(info) {
                    const holidayDetailsCard = document.getElementById('holidayDetailsCard');
                    const holidayDetailsContent = document.getElementById('holidayDetailsContent');

                    // Build holiday details
                    let details = `
                        <div class="holiday-detail">
                            <h5 class="text-primary mb-3">${info.event.title}</h5>
                            <p class="mb-2"><strong>Date:</strong>
                                ${info.event.start.toLocaleDateString('en-US', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                })}
                            </p>`;

                    if (info.event.extendedProps.description) {
                        details += `<p class="mb-2"><strong>Description:</strong> ${info.event.extendedProps.description}</p>`;
                    }

                    if (info.event.extendedProps.type) {
                        const typeBadge = info.event.extendedProps.type === 'weekly_holiday'
                            ? '<span class="badge bg-success">Weekly Holiday</span>'
                            : '<span class="badge bg-primary">Regular Holiday</span>';
                        details += `<p class="mb-0"><strong>Type:</strong> ${typeBadge}</p>`;
                    }

                    details += `</div>`;

                    // Admin এর জন্য Edit Button
                    if (isAdmin && info.event.url) {
                        details += `
                            <div class="mt-3 text-end">
                                <a href="${info.event.url}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit Holiday
                                </a>
                            </div>`;
                    }

                    // Show details
                    holidayDetailsContent.innerHTML = details;
                    holidayDetailsCard.classList.remove('d-none');

                    // Scroll to details
                    holidayDetailsCard.scrollIntoView({ behavior: 'smooth' });
                }
            });

            calendar.render();
        });
    </script>
@endsection
