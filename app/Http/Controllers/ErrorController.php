<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Illuminate\Http\JsonResponse;

class ErrorController extends Controller
{
    /**
     * Show latest error logs
     */
    public function index(): JsonResponse
    {
        $logs = ErrorLog::latest()->take(50)->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}
