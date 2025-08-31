<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Illuminate\Http\JsonResponse;

class ErrorController extends Controller
{
    /**
     * Show latest error logs (with pagination)
     */
    public function index(): JsonResponse
    {
        $logs = ErrorLog::latest()->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Show single error log
     */
    public function show($id): JsonResponse
    {
        $log = ErrorLog::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }
}
