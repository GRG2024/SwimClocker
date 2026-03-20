<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SwimPinAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $pin = config('swim.pin');

        // POST with pin = verify and set cookie
        if ($request->isMethod('post') && $request->has('pin')) {
            if ($request->input('pin') === $pin) {
                $token = hash('sha256', $pin . config('app.key'));

                return response()
                    ->json(['success' => true])
                    ->cookie('swim_auth', $token, 60 * 24 * 30); // 30 days
            }

            return response()->json(['success' => false, 'message' => 'Onjuiste PIN'], 401);
        }

        // Check cookie
        $token = $request->cookie('swim_auth');
        $expected = hash('sha256', $pin . config('app.key'));

        if ($token !== $expected) {
            return response()->json(['error' => 'auth_required'], 401);
        }

        return $next($request);
    }
}
