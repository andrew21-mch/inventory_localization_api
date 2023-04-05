<?php

namespace App\Exceptions;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

        if ($exception instanceof RouteNotFoundException) {
            return response()->json([
                'error' => 'not found',
                'success' => false,
                'message' => 'sorry, the resource you are looking for is not found'
            ], 404);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'error' => 'not found',
                'success' => false,
                'message' => 'sorry, the resource you are looking for is not found'
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'error' => 'not found',
                'success' => false,
                'message' => 'sorry, the resource you are looking for is not found'
            ], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'error' => 'method not allowed',
                'success' => false,
                'message' => 'sorry, the method you are using is not allowed'
            ], 405);
        }

        return parent::render($request, $exception);
    }

    protected function unauthorized($request, AuthorizationException $exception)
    {
        return response()->json([
            'error' => 'unauthorized',
            'success' => false,
            'message' => 'sorry, you are not authorized'
        ], 403);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'error' => 'unauthenticated',
            'success' => false,
            'message' => 'sorry, you are not authenticated'
        ], 401);
    }

    protected function prepareJsonResponse($request, Throwable $e): JsonResponse
    {
        return new JsonResponse(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    protected function convertExceptionToArray(Throwable $e): array
    {
        return config('app.debug') ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }

    protected function prepareResponse($request, Throwable $e): JsonResponse|Response|\Symfony\Component\HttpFoundation\Response
    {
        if ($e instanceof ValidationException && $e->response) {
            return $e->response;
        }

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        if ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        }

        if ($e instanceof AuthorizationException) {
            return $this->unauthorized($request, $e);
        }

        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof TokenMismatchException) {
            return redirect()->back()->withInput($request->input());
        }

        if ($e instanceof ValidationException && $e->getResponse()) {
            return $e->getResponse();
        }

        if ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        if ($this->isHttpException($e)) {
            return $this->toIlluminateResponse($this->renderHttpException($e), $e);
        }

        if ($e instanceof QueryException) {
            return $this->prepareResponse($request, new HttpException(500, $e->getMessage()));
        }

        if ($e instanceof PDOException) {
            return $this->prepareResponse($request, new HttpException(500, $e->getMessage()));
        }

        if ($e instanceof FatalThrowableError) {
            return $this->prepareResponse($request, new HttpException(500, $e->getMessage()));
        }

        return $this->prepareJsonResponse($request, $e);
    }

}
