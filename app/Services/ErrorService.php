<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Schema;

class ErrorService
{
    /**
     * Log an error or exception to the database.
     *
     * @param mixed $error Exception object or string message
     */
    public function log($error): void
    {
        try {
            if (!Schema::hasTable('error_logs')) {
                return;
            }

            if (is_object($error) && method_exists($error, 'getMessage')) {
                // Log exception object
                ErrorLog::create([
                    'message' => $error->getMessage(),
                    'trace'   => substr($error->getTraceAsString(), 0, 2000),
                    'file'    => $error->getFile(),
                    'line'    => $error->getLine(),
                ]);
            } else {
                // Log as simple message
                $this->logMessage($error);
            }
        } catch (\Exception $ex) {
            // Fail silently if logging fails
        }
    }

    /**
     * Log a custom message manually
     */
    public function logMessage(string $message, ?string $file = null, ?int $line = null): void
    {
        try {
            if (!Schema::hasTable('error_logs')) {
                return;
            }

            ErrorLog::create([
                'message' => $message,
                'file'    => $file,
                'line'    => $line,
            ]);
        } catch (\Exception $ex) {
            // Fail silently
        }
    }
}
