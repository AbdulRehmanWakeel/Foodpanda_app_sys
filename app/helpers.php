<?php

use App\Services\ErrorService;

if (!function_exists('log_error')) {
    /**
     * Log an error or exception to the database.
     *
     * @param mixed $error Exception or string message
     * @param string|null $file
     * @param int|null $line
     */
    function log_error($error, ?string $file = null, ?int $line = null)
    {
        $service = new ErrorService();

        // If $error is an exception (Throwable), log full details
        if (is_object($error) && method_exists($error, 'getMessage')) {
            $service->log($error);
        } else {
            // Otherwise, treat it as a manual message
            $service->logMessage($error, $file, $line);
        }
    }
}

