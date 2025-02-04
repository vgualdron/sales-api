<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Mail\PqrEmail;
use Illuminate\Support\Facades\Mail;

class PqrController extends Controller
{
    public function create(Request $request)
    {
        try {
            $userSesion = $request->user();
            $idUserSesion = $userSesion->id;
            $name = $request->name;
            $message = $request->message;

            $data = [
                'name' => $name,
                'document' => $document,
                'message' => $message,
            ];

            Mail::to('cooperativacoopserprog@gmail.com')->send(new PqrEmail($data));

            return response()->json([
                'message' => [
                    [
                        'text' => 'OK',
                        'detail' => 'PQR enviado con exito'
                    ]
                ]
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => 'OK',
                    'detail' => 'Guardado con éxito.'
                ]
            ],
            'data' => $item,
        ], Response::HTTP_OK);

    }

}
