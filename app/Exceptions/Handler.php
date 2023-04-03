<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception): JsonResponse|Response|\Symfony\Component\HttpFoundation\Response
    {
        if ($exception instanceof HttpException) {
            // check if its authentication exception
            if ($exception->getStatusCode() == 401) {
                return response()->json([
                    'error' => 'unauthenticated',
                    'success' => false,
                    'message' => 'sorry, you are not authenticated'
                ], 401);
            }

            // check if its authorization exception
            if ($exception->getStatusCode() == 403) {
                return response()->json([
                    'error' => 'unauthorized',
                    'success' => false,
                    'message' => 'sorry, you are not authorized'
                ], 403);
            }

            // check if its not found exception
            if ($exception->getStatusCode() == 404) {
                return response()->json([
                    'error' => 'not found',
                    'success' => false,
                    'message' => 'sorry, the resource you are looking for is not found'
                ], 404);
            }
        }

        if ($exception instanceof Exception) {
            return response()->json([
                'error' => $exception->getMessage(),
                'success' => false,
                'code' => $exception->getCode()
            ]);
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'error' => 'unauthenticated',
            'success' => false,
            'message' => 'sorry, you are not authenticated'
        ], 401);
    }
}
