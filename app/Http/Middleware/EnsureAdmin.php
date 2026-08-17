<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'محتاج تسجّل دخول.'], 401)
                : redirect()->guest(route('admin.login'));
        }

        // Deactivating a user has to log them out too, not just stop new logins.
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'الحساب ده موقوف. كلّم المدير.']);
        }

        return $next($request);
    }
}
