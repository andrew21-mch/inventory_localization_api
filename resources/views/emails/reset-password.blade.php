{{-- reset password email --}}
@component('mail::message')

# Hello {{ $user->name }}

You are receiving this email because we received a password reset request for your account.

Here is your password reset code: {{ $user->password_reset_code }}

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
