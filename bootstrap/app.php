<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureCan;
use App\Models\Redirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'can_' => EnsureCan::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        /*
         * Managed 301s are resolved here rather than in a global middleware:
         * a URL that still exists must never pay for a redirects lookup, and a
         * URL that doesn't is already on the error path.
         */
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            $path = Redirect::normalise($request->getPathInfo());

            $redirect = Redirect::query()
                ->where('is_active', true)
                ->where('from_path', $path)
                ->first();

            if (! $redirect) {
                return null;
            }

            $redirect->forceFill([
                'hits' => $redirect->hits + 1,
                'last_hit_at' => now(),
            ])->saveQuietly();

            return redirect()->to($redirect->to_path, $redirect->status_code);
        });
    })->create();
