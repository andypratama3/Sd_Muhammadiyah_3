<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Success response with data
     */
    protected function success($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Resource created successfully
     */
    protected function created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Resource updated successfully
     */
    protected function updated($data = null, string $message = 'Resource updated successfully'): JsonResponse
    {
        return $this->success($data, $message, 200);
    }

    /**
     * Resource deleted successfully
     */
    protected function deleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->success(null, $message, 200);
    }

    /**
     * Paginated response
     */
    protected function paginated(LengthAwarePaginator $paginator, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], 200);
    }

    /**
     * Error response
     */
    protected function error(string $message = 'Error', int $statusCode = 400, $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Bad request error
     */
    protected function badRequest(string $message = 'Bad request', $errors = null): JsonResponse
    {
        return $this->error($message, 400, $errors);
    }

    /**
     * Unauthorized error
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401, null);
    }

    /**
     * Forbidden error
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403, null);
    }

    /**
     * Not found error
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404, null);
    }

    /**
     * Conflict error
     */
    protected function conflict(string $message = 'Conflict occurred'): JsonResponse
    {
        return $this->error($message, 409, null);
    }

    /**
     * Validation error
     */
    protected function validationError(string $message = 'Validation failed', $errors = null): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Too many requests error
     */
    protected function tooManyRequests(string $message = 'Too many requests'): JsonResponse
    {
        return $this->error($message, 429, null);
    }

    /**
     * Server error
     */
    protected function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->error($message, 500, null);
    }
}
