<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\JsonResponse;

/**
 * Base API Controller with standardized response helpers.
 */
class Controller extends BaseController
{
    /**
     * Return a success response.
     */
    protected function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return an error response.
     */
    protected function error(string $message, int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a validation error response.
     */
    protected function validationError($errors): JsonResponse
    {
        return $this->error('Validation failed', 422, $errors);
    }

    /**
     * Return a not found error response.
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Return an unauthorized error response.
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Return a forbidden error response.
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }
}
