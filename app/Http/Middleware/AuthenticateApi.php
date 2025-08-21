<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi extends Middleware
{
    /**
     * Handle unauthenticated requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null|void
     */
    protected function redirectTo($request)
    {
        // If the request expects JSON or is an API route, return a JSON response
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data'    => null
            ], Response::HTTP_UNAUTHORIZED));
        }

        // Fallback for non-API web requests
        return route('login');
    }
}
