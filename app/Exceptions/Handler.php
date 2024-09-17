<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $exception) {
            if ($exception instanceof Exception) {
                $this->errorResult($exception);
            }
        });
    }

    protected function errorResult($exception)
    {
        $message = method_exists($exception, 'getMessage') ? $exception->getMessage() : "";
        $statusCode = $exception->getCode() > 0 ? $exception->getCode() : 500;
        $response = new Response([
            'status'  => false,
            'message' => $message,
        ], $statusCode);
        throw new ValidationException($message, $response);
    }
}
