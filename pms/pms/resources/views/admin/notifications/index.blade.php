{{-- Use dynamic layout based on user type --}}
@if(auth()->check() && auth()->user()->is_admin)
    @extends('admin.layout.app')
@else
    @extends('employee.layout.app')
@endif

@section('content')
<div class="container mt-4">
    <h4>{{ (auth()->check() && auth()->user()->is_admin) ? 'All Notifications' : 'My Notifications' }}</h4>

    <div class="d-flex gap-2 mb-3">
        <button id="markAllBtn" class="btn btn-sm btn-success">Mark All as Read</button>

        <form id="clearAllForm" action="{{ route('notifications.clearAll') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all notifications?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger">Clear All</button>
        </form>
    </div>

    @if($notifications->count() > 0)
        <ul class="list-group" id="notificationsList">
            @foreach($notifications as $notification)
                @php
                    // decode data
                    $data = is_array($notification->data) ? $notification->data : (json_decode($notification->data, true) ?? []);
                    $title = $data['title'] ?? $data['heading'] ?? 'Notification';
                    $message = $data['message'] ?? $data['body'] ?? $data['msg'] ?? '';
                    $isUnread = is_null($notification->read_at);

                    // DYNAMIC URL based on user type
                    if (isset($data['ticket_id'])) {
                        $link = (auth()->check() && auth()->user()->is_admin)
                            ? url('admin/tickets/' . $data['ticket_id'])
                            : url('employee/tickets/' . $data['ticket_id']);
                    } else {
                        $link = $data['url'] ?? null;
                    }
                @endphp

                <li class="list-group-item d-flex justify-content-between align-items-start {{ $isUnread ? 'fw-bold' : '' }}">
                    <div class="d-flex gap-3 align-items-start">
                        <div style="width:46px; flex:0 0 46px;">
                            <i class="fa fa-bell fa-lg text-secondary"></i>
                        </div>

                        <div>
                            <div class="fw-semibold mb-1">
                                @if($link)
                                    <a href="{{ $link }}" style="text-decoration:none; color:inherit;">
                                        {{ e($title) }}
                                    </a>
                                @else
                                    {{ e($title) }}
                                @endif
                            </div>

                            @if($message)
                                <div class="text-muted small mb-1">{{ e($message) }}</div>
                            @endif

                            <div class="text-muted small">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    <div class="ms-3 text-end d-flex flex-column align-items-end gap-2">
                        @if($isUnread)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="d-inline mark-read-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary mark-read-btn">Mark read</button>
                            </form>
                        @else
                            <span class="badge bg-success">Read</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="alert alert-info">
            No notifications found.
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const tokenMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '{{ csrf_token() }}';

  async function postJson(url, body = {}) {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(body)
    });
    return res.json();
  }

  // Mark all as read
  const markAllBtn = document.getElementById('markAllBtn');
  if (markAllBtn) {
    markAllBtn.addEventListener('click', async function () {
      try {
        const result = await postJson('{{ route("notifications.readAll") }}', {});
        if (result.status === 'ok') {
          // Reload page to show updated status
          window.location.reload();
        }
      } catch (error) {
        console.error(error);
      }
    });
  }

  // Clear all notifications
  const clearAllForm = document.getElementById('clearAllForm');
  if (clearAllForm) {
    clearAllForm.addEventListener('submit', function (e) {
      if (!confirm('Are you sure you want to clear all notifications?')) {
        e.preventDefault();
      }
    });
  }

  // Mark single notification as read
  document.querySelectorAll('.mark-read-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'ok') {
          window.location.reload();
        }
      })
      .catch(error => {
        console.error(error);
        this.submit(); // Fallback to normal form submission
      });
    });
  });
});
</script>
@endpush
