<?php

namespace App\Exceptions;

// use App\Http\Traits\ApiResponse;
use Src\Shared\Presentation\Http\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Src\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\Log;
use Src\Shared\Logging\TraceContext;
use Src\Shared\Logging\Traits\LogsDomainEvents;

use Throwable;

//use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Handler extends ExceptionHandler
{
    use ApiResponse, LogsDomainEvents;

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
        $this->reportable(function (Throwable $e) {
          
            $this->logError(
                message: $e->getMessage(),
                errorCode: 'UNHANDLED_EXCEPTION',
                entityId: null,
                exception: $e
            );

        })->stop();       

    }

    public function render($request, Throwable $e)
    {
        return $this->handleException($request, $e);;
    }

    public function handleException($request, Throwable $exception)
    {

        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('No se encontró la URL especificada', 404);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->errorResponse('No se encontró el recurso especificado', 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('El método especificado en la petición no es válido', 405);
        }

        if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        // if (config('app.debug')) {
        //     return parent::render($request, $exception);
        // }

        // return $this->errorResponse($exception->getMessage(), $exception->getCode() ? $exception->getCode() : 500);

        if (config('app.debug')) {
            return parent::render($request, $exception);
        }

        $errorCode = $exception instanceof DomainException
            ? $exception->errorCode()
            : 'UNHANDLED_EXCEPTION';

        return $this->errorResponse(
            $exception->getMessage(), //'Internal server error',
            500,
            $errorCode
        );


    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->isFrontend($request)) {
            return redirect()->guest('login');
        }
        return $this->errorResponse('No autenticado.', 401);
    }

    protected function convertValidationExceptionToResponse(
        ValidationException $e,
        $request
    ): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response {
        $errors = $e->validator->errors()->getMessages();

        if ($this->isFrontend($request)) {
            return $request->ajax() ? response()->json($errors, 422) : redirect()
                ->back()
                ->withInput($request->input())
                ->withErrors($errors);
        }
 
        return $this->errorResponse(
            message: 'Validation failed',
            code: 422,
            errorCode: 'VALIDATION_ERROR',
            errors: $errors
        );
    }

    /**
     * @param $request
     * @return bool
     */
    private function isFrontend($request): bool
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }

}
