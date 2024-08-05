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
            if ($e instanceof ModelNotFoundException ||
                $e instanceof NotFoundHttpException ||
                $e instanceof AuthorizationException ||
                $e instanceof AccessDeniedHttpException) {
                return response()->view('errors.404', [], 404);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // // Handle ModelNotFoundException
        // if ($exception instanceof ModelNotFoundException) {
        //     return response()->view('errors.404', [], 404);
        // }

        // // Handle NotFoundHttpException (for undefined routes)
        // if ($exception instanceof NotFoundHttpException) {
        //     return response()->view('errors.404', [], 404);
        // }

        // // Handle AuthorizationException and AccessDeniedHttpException (access denied)
        // if ($exception instanceof AuthorizationException || $exception instanceof AccessDeniedHttpException) {
        //     return response()->view('errors.404', [], 404);
        // }
        return parent::render($request, $exception);

    }
}
