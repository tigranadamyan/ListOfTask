<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Render a generic error response.
 * In production, hides internal details. In debug mode, includes the message.
 */
if (! function_exists('renderGenericError')) {
    function renderGenericError(Throwable $e): Illuminate\Http\JsonResponse
    {
        $isDebug = app()->hasDebugModeEnabled();

        return response()->json([
            'error' => [
                'code' => $e instanceof HttpException ? $e->getStatusCode() : 500,
                'message' => $isDebug ? $e->getMessage() : 'Internal server error.',
                ...($isDebug ? ['file' => $e->getFile(), 'line' => $e->getLine()] : []),
            ],
        ], $e instanceof HttpException ? $e->getStatusCode() : 500);
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 401 — Unauthenticated
        $exceptions->render(fn (AuthenticationException $e, Request $request) => response()->json([
            'error' => ['code' => 401, 'message' => $e->getMessage() ?: 'Unauthenticated.'],
        ], 401));

        // 403 — Forbidden (Policy failures)
        $exceptions->render(fn (AccessDeniedHttpException $e, Request $request) => response()->json([
            'error' => ['code' => 403, 'message' => $e->getMessage() ?: 'This action is unauthorized.'],
        ], 403));

        // 404 — Model not found
        $exceptions->render(fn (ModelNotFoundException $e, Request $request) => response()->json([
            'error' => ['code' => 404, 'message' => 'Resource not found.'],
        ], 404));

        // 404 — Route not found
        $exceptions->render(fn (NotFoundHttpException $e, Request $request) => response()->json([
            'error' => ['code' => 404, 'message' => 'The requested endpoint does not exist.'],
        ], 404));

        // 422 — Validation errors
        $exceptions->render(fn (ValidationException $e, Request $request) => response()->json([
            'error' => ['code' => 422, 'message' => 'Validation failed.', 'details' => $e->errors()],
        ], 422));

        // 429 — Rate limiting
        $exceptions->render(fn (ThrottleRequestsException $e, Request $request) => response()->json([
            'error' => ['code' => 429, 'message' => 'Too many requests. Please try again later.'],
        ], 429));

        // 500 — All other unhandled exceptions
        $exceptions->render(fn (Throwable $e) => renderGenericError($e));
    })->create();
