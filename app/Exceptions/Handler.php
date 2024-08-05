<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e) {
            // Additional custom exception handling can be added here if needed
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle ModelNotFoundException
        if ($exception instanceof ModelNotFoundException) {
            return new NotFoundHttpException();
        }

        // Handle NotFoundHttpException (for undefined routes)
        if ($exception instanceof NotFoundHttpException) {
            return new NotFoundHttpException();
        }

        // Handle AuthorizationException and AccessDeniedHttpException (access denied)
        if ($exception instanceof AuthorizationException) {
            return new NotFoundHttpException();
        }

        if ($exception instanceof AccessDeniedHttpException) {
            return new NotFoundHttpException();
        }


    }
}
