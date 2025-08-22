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

    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {

                 
                try {
                    ErrorLog::create([
                        'message' => $e->getMessage(),
                        'trace'   => $e->getTraceAsString(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ]);
                } catch (\Throwable $logError) {
                    // avoid infinite loop if DB insert fails
                }

                 
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

                 
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong, please try again later.',
                ], 500);
            }
        });
    }
}
