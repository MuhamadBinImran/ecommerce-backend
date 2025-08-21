<?php

namespace App\Services\Admin;

use App\Interfaces\AdminAuthInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\ErrorLogService;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAuthService implements AdminAuthInterface
{
    protected ErrorLogService $errorLogger;

    public function __construct(ErrorLogService $errorLogger)
    {
        $this->errorLogger = $errorLogger;
    }

    /**
     * Login user (admin, customer, seller)
     *
     * @param array $data ['email', 'password']
     * @param string|null $role Optional: 'admin', 'customer', 'seller'
     * @return array
     */
    public function login(array $data, ?string $role = null): array
    {
        try {
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                return $this->response(false, 'User not found');
            }

            if ($user->is_blocked) {
                return $this->response(false, 'Your account has been blocked. Contact support.');
            }

            // Role check only if role is passed
            if ($role && !$user->hasRole($role)) {
                return $this->response(false, "Unauthorized: Not a {$role}");
            }

            if (!Hash::check($data['password'], $user->password)) {
                return $this->response(false, 'Incorrect password');
            }

            $token = Auth::guard('api')->login($user);

            return $this->response(true, 'Login successful', [
                'user'  => $user,
                'roles' => $user->getRoleNames(), // return all roles
                'token' => $token,
            ]);

        } catch (\Throwable $e) {
            $this->errorLogger->log($e, ['context' => $data]);
            return $this->response(false, 'Login failed due to server error', null, $e->getMessage());
        }
    }

    /**
     * Logout
     */
    public function logout(): array
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) return $this->response(false, 'No token provided');

            JWTAuth::invalidate($token);
            return $this->response(true, 'Logout successful');

        } catch (\Throwable $e) {
            $this->errorLogger->log($e, ['context' => 'logout']);
            return $this->response(false, 'Logout failed', null, $e->getMessage());
        }
    }

    private function response(bool $success, string $message, $data = null, ?string $error = null): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'error'   => $error,
        ];
    }
}
