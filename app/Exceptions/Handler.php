<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
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

    public function shouldReturnJson($request, Throwable $e){
        return true;
    }


    public function render($request, Throwable $exception)
    {
        // Xử lí trả về cho validate form request
        if ($exception instanceof ValidationException) {
            // Trường hợp APP_ENV = production
            if (app()->environment('production')) {
                return response()->json([
                    'status' => false,
                    'message' => $exception->getMessage(), 
                ], 422); 
            } else {
                // Trường hợp APP_ENV != production
                return response()->json([
                    'status' => false,
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ], 422);
            }
        }

      
        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'status' => false,
            'message' => 'Bạn không có quyền truy cập!'
        ], 401);
    }
}
