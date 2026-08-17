<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level permission check: `->middleware('can_:orders.edit')`.
 *
 * Reads the same matrix as User::can_(), so a route and the nav item that links
 * to it can never disagree about who may open it.
 */
class EnsureCan
{
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $user = Auth::user();

        foreach ($abilities as $ability) {
            if ($user && $user->can_($ability)) {
                return $next($request);
            }
        }

        abort(403, 'الصفحة دي مش من صلاحياتك.');
    }
}
