<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Throwable;

class ErrorService
{
    /**
     * Log an exception or string message to the database.
     *
     * @param Throwable|string $error
     * @param Request|null $request
     */
    public function log($error, ?Request $request = null): void
    {
        try {
            if (!Schema::hasTable('error_logs')) {
                return; // Table does not exist
            }

            $data = [
                'message' => is_object($error) && method_exists($error, 'getMessage')
                    ? $error->getMessage()
                    : (string)$error,
                'trace' => is_object($error) && method_exists($error, 'getTraceAsString')
                    ? substr($error->getTraceAsString(), 0, 2000)
                    : 'No trace available',
                'file' => is_object($error) && method_exists($error, 'getFile')
                    ? $error->getFile()
                    : 'N/A',
                'line' => is_object($error) && method_exists($error, 'getLine')
                    ? $error->getLine()
                    : null,
                'url' => $request ? $request->fullUrl() : 'CLI/Manual',
                'method' => $request ? $request->method() : 'N/A',
                'request_data' => $request ? json_encode($request->all()) : null,
                'type' => is_object($error) ? get_class($error) : 'ManualError',
            ];

            ErrorLog::create($data);

        } catch (\Exception $ex) {
            // Fail silently if logging fails
        }
    }
}
