<?php

namespace App\Exceptions;

use App\Traits\ApiResponses;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponses;

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

    protected $handlers = [
        ValidationException::class => 'handleValidation',
        ModelNotFoundException::class => 'handleModelNotFound',
        AuthenticationException::class => 'handleAuthentication'
    ];

    private function handleValidation(ValidationException $exception)
    {
        $errors = [];

        foreach($exception->errors() as $key => $value) {
            foreach($value as $message) {
                $errors[] = [
                    'status' => 422,
                    'message' => $message,
                    'source' => $key
                ];
            }
        }

        return $errors;
    }

    private function handleModelNotFound(ModelNotFoundException $exception)
    {
        return [
            [
                'status' => 404,
                'message' => 'The resource cannot be found.',
                'source' => null
            ]
        ];
    }

    private function handleAuthentication(AuthenticationException $exception)
    {
        return [
            [
                'status' => 401,
                'message' => 'Unauthenticated',
                'source' => null
            ]
        ];
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        $className = get_class($exception);
        
        if (isset($this->handlers[$className])) {
            $method = $this->handlers[$className];
            return $this->error( $this->$method($exception) );
        }

        $index = strrpos($className, '\\');

        return $this->error([
            [
                'type' => substr($className, $index + 1),
                'status' => 0,
                'message' => $exception->getMessage(),
                'source' => null
            ]
        ]);
    }
}
