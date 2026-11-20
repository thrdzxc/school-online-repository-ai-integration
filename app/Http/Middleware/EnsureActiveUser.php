<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Prevent a disabled account from using an already-established browser session. */
class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->active) {
            if ($request->expectsJson() || !$request->hasSession()) {
                return response()->json(['message' => 'Your account is inactive. Contact the administrator.'], 403);
            }
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is inactive. Contact the administrator.',
            ]);
        }
        return $next($request);
    }
}
