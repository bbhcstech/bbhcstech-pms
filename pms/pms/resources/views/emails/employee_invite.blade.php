@component('mail::message')
# Hello {{ $user->name ?? 'there' }},

@if(!empty($messageText))
{!! nl2br(e($messageText)) !!}
@else
Welcome to Xinksoft Technologies Pvt. Ltd.
@endif

@component('mail::button', ['url' => $inviteLink])
Accept Invite & Set Up Account
@endcomponent

If the button above does not work, copy and paste this URL into your browser:
{{ $inviteLink }}

Thanks,<br>
Xinksoft Team
@endcomponent
