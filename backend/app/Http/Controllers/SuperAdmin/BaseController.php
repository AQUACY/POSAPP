<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Base controller for all Super Admin controllers
 * Provides common functionality and response methods
 */
class BaseController extends Controller
{
    /**
     * Send a success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function sendSuccess($data, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Send an error response
     *
     * @param string $message
     * @param mixed $errors
     * @param int $code
     * @return JsonResponse
     */
    protected function sendError(string $message, $errors = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Check if the authenticated user is a super admin
     *
     * @return bool
     */
    protected function isSuperAdmin(): bool
    {
        return auth()->user()->isSuperAdmin();
    }
} 