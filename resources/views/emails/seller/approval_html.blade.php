<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $approved ? 'Seller Account Approved 🎉' : 'Seller Account Unapproved ⚠️' }}</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #eef1f7; margin: 0; padding: 0;">

<!-- Outer Wrapper -->
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef1f7; padding:40px 0;">
    <tr>
        <td align="center">

            <!-- Email Container -->
            <table width="650" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 18px rgba(0,0,0,0.1);">

                <!-- Header -->
                <tr>
                    <td style="background:linear-gradient(90deg, #0d6efd, #6610f2); text-align:center; padding:25px;">
                        <img src="https://yourdomain.com/logo.png" alt="{{ config('app.name') }} Logo" style="max-height:55px;">
                        <h1 style="color:#fff; margin:10px 0 0; font-size:22px; letter-spacing:1px;">
                            {{ config('app.name') }}
                        </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:40px 35px;">
                        <h2 style="color:#222; margin-bottom:15px; font-size:24px; font-weight:600;">
                            Hello {{ $seller->name }},
                        </h2>

                        @if($approved)
                            <p style="font-size:16px; color:#444; line-height:1.6;">
                                🎉 <strong>Great news!</strong> <br>
                                Your seller account has been
                                <span style="color:green; font-weight:bold;">approved</span>.
                                You can now upload products and start selling on <strong>{{ config('app.name') }}</strong>.
                            </p>
                        @else
                            <p style="font-size:16px; color:#444; line-height:1.6;">
                                ⚠️ <strong>Important Notice:</strong> <br>
                                Unfortunately, your seller account has been
                                <span style="color:red; font-weight:bold;">unapproved</span>.
                                Please reach out to our <a href="mailto:support@yourdomain.com" style="color:#0d6efd; text-decoration:none;">support team</a> for assistance.
                            </p>
                        @endif

                        <!-- Action Button -->
                        <div style="text-align:center; margin:35px 0;">
                            <a href="{{ config('app.url') }}/dashboard"
                               style="background:linear-gradient(90deg, #0d6efd, #6610f2); color:#fff; padding:14px 28px; text-decoration:none; border-radius:8px; font-size:16px; font-weight:500; display:inline-block; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                                Go to Dashboard →
                            </a>
                        </div>

                        <p style="font-size:14px; color:#666; line-height:1.5;">
                            Thanks for being part of our community!
                            <br> – The <strong>{{ config('app.name') }}</strong> Team
                        </p>
                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td style="padding:0 35px;">
                        <hr style="border:none; border-top:1px solid #ddd; margin:0;">
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#fafafa; text-align:center; padding:20px; font-size:12px; color:#777;">
                        <p style="margin:5px 0;">
                            📍 {{ config('app.name') }} | All rights reserved © {{ date('Y') }}
                        </p>
                        <p style="margin:5px 0;">
                            <a href="{{ config('app.url') }}/privacy" style="color:#0d6efd; text-decoration:none;">Privacy Policy</a> •
                            <a href="{{ config('app.url') }}/support" style="color:#0d6efd; text-decoration:none;">Support</a>
                        </p>
                    </td>
                </tr>
            </table>
            <!-- End Container -->

        </td>
    </tr>
</table>

</body>
</html>
