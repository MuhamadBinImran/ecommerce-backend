<?php

namespace App\Services\Customer;

use App\Interfaces\CustomerRegistrationInterface;
use App\Mail\CustomerOtpMail;
use App\Models\CustomerProfile;
use App\Models\CustomerRegistrationToken;
use App\Models\User;
use App\Services\ErrorLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Illuminate\Support\Facades\Log;

class CustomerRegistrationService implements CustomerRegistrationInterface
{
    private const OTP_TTL_MINUTES = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_ATTEMPTS = 5;

    public function __construct(private ErrorLogService $logger) {}



    // app/Services/Customer/CustomerRegistrationService.php

    public function requestOtp(array $data): array
    {
        try {
            Log::info('Requesting OTP', ['email' => $data['email']]);

            if (User::where('email', $data['email'])->exists()) {
                return ['success'=>false,'message'=>'Email already registered.','data'=>null];
            }

            $otp = (string)random_int(100000, 999999);
            Log::info('Generated OTP', ['email' => $data['email'], 'otp' => $otp]);

            $now = now();
            $payload = [
                'phone'       => $data['phone'] ?? null,
                'address'     => $data['address'] ?? null,
                'city'        => $data['city'] ?? null,
                'state'       => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country'     => $data['country'] ?? null,
            ];

            // Create or update the token
            $token = CustomerRegistrationToken::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'          => $data['name'],
                    'password_hash' => Hash::make($data['password']),
                    'payload'       => $payload,
                    'otp_hash'      => Hash::make($otp),
                    'expires_at'    => $now->copy()->addMinutes(self::OTP_TTL_MINUTES),
                    'attempts'      => 0,
                    'last_sent_at'  => $now,
                    'used_at'       => null,
                ]
            );

            Log::info('Prepared CustomerRegistrationToken', ['email' => $data['email'], 'token_id' => $token->id]);

            // Send mail
            Mail::to($token->email)->send(new CustomerOtpMail($token->name, $otp));
            Log::info('OTP mail send invoked', ['email' => $token->email]);

            return [
                'success' => true,
                'message' => 'OTP sent to your email.',
                'data'    => ['expires_in_minutes' => self::OTP_TTL_MINUTES]
            ];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action'=>'customer_request_otp','email'=>$data['email'] ?? null]);
            Log::error('OTP request failed', ['error' => $e->getMessage()]);
            return [
                'success'=>false,
                'message'=>'Could not send OTP.',
                'error'=>config('app.debug') ? $e->getMessage() : 'ERR_SEND_OTP'
            ];
        }
    }



    public function verifyOtp(string $email, string $otp): array
    {
        try {
            $token = CustomerRegistrationToken::where('email', $email)->first();

            if (!$token || $token->used_at) {
                return ['success'=>false,'message'=>'Invalid or already used token.','data'=>null];
            }

            if (now()->gt($token->expires_at)) {
                return ['success'=>false,'message'=>'OTP expired. Please request a new one.','data'=>null];
            }

            if ($token->attempts >= self::MAX_ATTEMPTS) {
                return ['success'=>false,'message'=>'Too many attempts. Please request a new code.','data'=>null];
            }

            // increment attempts
            $token->increment('attempts');

            if (!Hash::check($otp, $token->otp_hash)) {
                return ['success'=>false,'message'=>'Incorrect OTP.','data'=>['remaining_attempts'=>max(0, self::MAX_ATTEMPTS - $token->attempts)]];
            }

            // Passed — create user & profile in a transaction
            $result = DB::transaction(function () use ($token) {
                // guard against race
                if (User::where('email', $token->email)->exists()) {
                    return ['success'=>false,'message'=>'Email already registered.','data'=>null];
                }

                $user = User::create([
                    'name'     => $token->name,
                    'email'    => $token->email,
                    'password' => $token->password_hash, // already hashed; cast won't rehash
                ]);

                // assign role
                if (method_exists($user, 'assignRole')) {
                    try { $user->assignRole('customer'); } catch (Throwable $roleEx) {}
                }

                // create profile
                $payload = $token->payload ?? [];
                $profile = CustomerProfile::create([
                    'user_id'     => $user->id,
                    'phone'       => $payload['phone'] ?? null,
                    'address'     => $payload['address'] ?? null,
                    'city'        => $payload['city'] ?? null,
                    'state'       => $payload['state'] ?? null,
                    'postal_code' => $payload['postal_code'] ?? null,
                    'country'     => $payload['country'] ?? null,
                ]);

                // mark token used
                $token->update(['used_at' => now()]);

                // OPTIONAL: auto-login (issue JWT)
                // If you don’t want auto-login, just set $jwt = null;
                $jwt = auth()->login($user);

                return [
                    'success' => true,
                    'message' => 'Registration completed.',
                    'data' => [
                        'user'    => $user->only(['id','name','email']),
                        'profile' => $profile->only(['id','phone','address','city','state','postal_code','country']),
                        'token'   => $jwt,
                    ],
                ];
            });

            return $result;
        } catch (Throwable $e) {
            $this->logger->log($e, ['action'=>'customer_verify_otp','email'=>$email]);
            return ['success'=>false,'message'=>'Verification failed.','error'=>config('app.debug')?$e->getMessage():'ERR_VERIFY_OTP'];
        }
    }

    public function resendOtp(string $email): array
    {
        try {
            $token = CustomerRegistrationToken::where('email', $email)->first();

            if (!$token || $token->used_at) {
                return ['success'=>false,'message'=>'No pending registration found for this email.','data'=>null];
            }

            if (User::where('email', $email)->exists()) {
                return ['success'=>false,'message'=>'Email already registered.','data'=>null];
            }

            $now = now();
            if ($token->last_sent_at && $token->last_sent_at->diffInSeconds($now) < self::RESEND_COOLDOWN_SECONDS) {
                return ['success'=>false,'message'=>'Please wait before requesting another code.','data'=>null];
            }

            $otp = (string)random_int(100000, 999999);
            $token->update([
                'otp_hash'     => Hash::make($otp),
                'expires_at'   => $now->copy()->addMinutes(self::OTP_TTL_MINUTES),
                'attempts'     => 0,
                'last_sent_at' => $now,
            ]);

            Mail::to($token->email)->send(new CustomerOtpMail($token->name, $otp));

            return ['success'=>true,'message'=>'OTP re-sent to your email.','data'=>['expires_in_minutes'=>self::OTP_TTL_MINUTES]];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action'=>'customer_resend_otp','email'=>$email]);
            return ['success'=>false,'message'=>'Could not resend OTP.','error'=>config('app.debug')?$e->getMessage():'ERR_RESEND_OTP'];
        }
    }
}
