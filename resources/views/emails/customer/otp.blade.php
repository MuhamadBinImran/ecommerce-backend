@component('mail::message')
    {{-- Header --}}
    # 🔐 Verify Your Identity

    Hello **{{ $name }}**,

    We received a request to verify your identity. Please use the code below:

    {{-- OTP Box --}}
    @component('mail::panel')
        <h2 style="color:#1d4ed8; text-align:center; font-size:28px; letter-spacing:4px; font-weight:bold;">
            {{ $code }}
        </h2>
    @endcomponent

    This **One-Time Password (OTP)** will expire in **10 minutes**.

    If you didn’t request this verification, you can safely ignore this email.

    {{-- Call to Action (optional if you want to guide them back to app/site) --}}
    @component('mail::button', ['url' => config('app.url'), 'color' => 'blue'])
        Go to {{ config('app.name') }}
    @endcomponent

    Thanks for securing your account with us,
    **The {{ config('app.name') }} Team**

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">

    <small style="color:#6b7280;">
        ⚠️ Do not share this code with anyone. {{ config('app.name') }} staff will never ask for your verification code.
    </small>
@endcomponent
