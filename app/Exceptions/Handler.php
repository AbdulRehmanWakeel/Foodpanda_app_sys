<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Services\ErrorService;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    protected $levels = [];
    protected $dontReport = [];
    protected $dontFlash = ['current_password', 'password'];

    public function register(): void
    {
        //
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return JsonResponse
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        // Log all exceptions to error_logs table
        $service = app(ErrorService::class);
        $service->log($exception, $request);

        // Determine status code
        $status = 500;
        if (method_exists($exception, 'getStatusCode')) {
            $status = $exception->getStatusCode();
        }

        // Return clean JSON response
        return response()->json([
            'success' => false,
            'error' => $exception->getMessage(),
        ], $status);
    }
}
