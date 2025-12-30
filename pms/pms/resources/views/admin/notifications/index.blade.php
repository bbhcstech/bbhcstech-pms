@extends('admin.layout.app')

@section('content')
<div class="container mt-4">
    <h4>All Notifications</h4>

    <div class="d-flex gap-2 mb-3">
        <button id="markAllBtn" class="btn btn-sm btn-success">Mark All as Read</button>

        <form id="clearAllForm" action="{{ route('notifications.clearAll') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all notifications?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger">Clear All</button>
        </form>
    </div>

    <ul class="list-group" id="notificationsList">
        @forelse($notifications as $notification)
            @php
                // decode data whether it's JSON string or already array
                $data = is_array($notification->data) ? $notification->data : (json_decode($notification->data, true) ?? []);
                $title = $data['title'] ?? $data['heading'] ?? 'Notification';
                $message = $data['message'] ?? $data['body'] ?? $data['msg'] ?? '';
                $isUnread = is_null($notification->read_at);
                // build URL: prefer explicit url in payload, fallback to ticket page if ticket_id present
                $link = $data['url'] ?? (isset($data['ticket_id']) ? url('admin/tickets/' . $data['ticket_id']) : null);
            @endphp

            <li class="list-group-item d-flex justify-content-between align-items-start {{ $isUnread ? 'fw-bold' : '' }}" data-id="{{ $notification->id }}" data-data='@json($data)'>
                <div class="d-flex gap-3 align-items-start">
                    <div style="width:46px; flex:0 0 46px;">
                        <i class="fa fa-bell fa-lg text-secondary" aria-hidden="true"></i>
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
        @empty
            <li class="list-group-item text-muted">No notifications</li>
        @endforelse
    </ul>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
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

  // Mark all via AJAX (uses your route name)
  const markAllBtn = document.getElementById('markAllBtn');
  if (markAllBtn) {
    markAllBtn.addEventListener('click', async function () {
      try {
        const d = await postJson('{{ route("notifications.readAll") }}', {});
        if (d.status === 'ok' || d === true) {
          document.querySelectorAll('#notificationsList li').forEach(li => {
            li.classList.remove('fw-bold');
            const btn = li.querySelector('.mark-read-form');
            if (btn) btn.replaceWith(Object.assign(document.createElement('span'), { className:'badge bg-success', innerText:'Read'}));
          });
        } else {
          console.error('markAll response', d);
        }
      } catch (err) {
        console.error(err);
      }
    });
  }

  // Clear all via AJAX (progressive enhancement)
  const clearAllForm = document.getElementById('clearAllForm');
  if (clearAllForm) {
    clearAllForm.addEventListener('submit', function (e) {
      if (!window.fetch) return true;
      e.preventDefault();
      if (!confirm('Are you sure you want to clear all notifications?')) return false;

      postJson('{{ route("notifications.clearAll") }}', {})
        .then(d => {
          if (d.status === 'ok') {
            const list = document.getElementById('notificationsList');
            if (list) {
              list.innerHTML = '<li class="list-group-item text-muted">No notifications</li>';
            } else {
              location.reload();
            }
          } else {
            console.error('clearAll response', d);
            clearAllForm.submit();
          }
        })
        .catch(err => {
          console.error(err);
          clearAllForm.submit();
        });
    });
  }

  // Per-item AJAX mark as read
  document.querySelectorAll('.mark-read-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const action = form.getAttribute('action');
      postJson(action, {})
        .then(d => {
          if (d.status === 'ok' || d === true) {
            const li = form.closest('li');
            if (li) {
              li.classList.remove('fw-bold');
              form.replaceWith(Object.assign(document.createElement('span'), { className:'badge bg-success', innerText:'Read'}));
            } else {
              location.reload();
            }
          } else {
            console.error('mark-read response', d);
          }
        }).catch(console.error);
    });
  });

});
</script>
@endpush
