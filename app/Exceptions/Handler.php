<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;

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
        $this->renderable(function (\Throwable $e, $request) {
            if ($e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'No tienes permiso para realizar esta acción',
                            'detail' => 'Para obtener autorización, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_FORBIDDEN);
            } else if ($e instanceof \PDOException) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un problema al conectar con la base de datos',
                            'detail' => 'Por favor informe a un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }
}
