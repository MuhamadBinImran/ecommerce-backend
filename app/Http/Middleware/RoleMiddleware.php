<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ErrorLogService;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    protected ErrorLogService $errorLogService;

    public function __construct(ErrorLogService $errorLogService)
    {
        $this->errorLogService = $errorLogService;
    }

    /**
     * Handle role-based authorization
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        try {
            $user = $request->user();

            if (!$user || !method_exists($user, 'hasRole') || !$user->hasRole($role)) {
                // Log unauthorized access attempt
                $this->errorLogService->log(
                    new \Exception("Unauthorized access attempt."),
                    [
                        'user_id' => $user?->id,
                        'required_role' => $role,
                        'route' => $request->path(),
                        'method' => $request->method(),
                    ]
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: insufficient permissions.',
                    'data'    => null
                ], Response::HTTP_FORBIDDEN);
            }

            return $next($request);
        } catch (\Throwable $e) {
            $this->errorLogService->log($e, [
                'middleware' => 'RoleMiddleware',
                'role' => $role,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authorization failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error',
                'data'    => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
