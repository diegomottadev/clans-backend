<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    // Excepciones relacionadas a peticiones https de cualquier indole
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException){
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if ($exception instanceof ModelNotFoundException){
            $modelo = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("No existe una instancia de {$modelo} con el id identificado",400);
        }

        if ($exception instanceof AuthenticationException){
            return $this->unauthenticated($request, $exception);
        }


        if ($exception instanceof AuthorizationException){
            return $this->errorResponse('No posee permisos para ejecutar esta acción',403);
        }


        if ($exception instanceof NotFoundHttpException){
            return $this->errorResponse('No se encontro la url especificada',404);
        }

        if ($exception instanceof MethodNotAllowedHttpException){
            return $this->errorResponse('El método especificado en la petición no es válido',405);
        }

        if ($exception instanceof HttpException){
            return $this->errorResponse($exception->getMessage(),$exception->getStatusCode());
        }

        if ($exception instanceof QueryException){
            $codigo = $exception->errorInfo[1];
            if ($codigo ==1451){
                return $this->errorResponse("No se puede eliminar de forma permanente el recurso porque esta relacionado con algún otro.",409);
            }
        }
        //Falla inesperada
        if (config('app.debug')){
            return parent::render($request, $exception);
        }
        return $this->errorResponse("Falla inesperada, Intente luego.",409);

    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();

        return response()->json($errors, 422);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse('No autenticado', 401);
    }
}
