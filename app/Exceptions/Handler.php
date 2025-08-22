<?php

namespace App\Exceptions;

use App\Models\ErrorLog;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = ['password'];

    /**
     * Log every error into DB (only once).
     */
    public function report(Throwable $e): void
    {
        try {
            ErrorLog::create([
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'trace'     => substr($e->getTraceAsString(), 0, 2000), // truncate for safety
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);
        } catch (\Throwable $logError) {
            // Fail silently if DB logging fails
        }

        parent::report($e);
    }

    /**
     * Always return clean JSON in API responses.
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                ], 404);
            }

            if ($e instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                ], 405);
            }

            // Default: catch all others
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong, please try again later.',
            ], 500);
        }

        // For web requests, use Laravel’s default handling
        return parent::render($request, $e);
    }
}
