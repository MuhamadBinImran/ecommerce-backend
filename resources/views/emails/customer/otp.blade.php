@component('mail::message')
    # Hi {{ $name }},

    Your verification code is:

    # **{{ $code }}**

    This code expires in 10 minutes. If you didn’t request this, you can ignore this email.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
