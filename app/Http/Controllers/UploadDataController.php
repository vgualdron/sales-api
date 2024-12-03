<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Novel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UploadDataController extends Controller
{
    public function createNew(Request $request)
    {
        try {
            $data = $request->all();
            Novel::create($data);
            
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function listNews(Request $request)
    {
        try {
            $items = Novel::where('type_cv', 'pdf')->orderBy('created_at', 'desc')->get();
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed',
        ], JsonResponse::HTTP_OK);
    }

    public function uploadPayments(Request $request)
    {
        try {
            $data = $request->data;
            DB::transaction(function () use ($data) {
                foreach ($data as $item) {
                    Payment::create([
                        'reference' => $item['value'], // Cambia según los datos del array
                        'date' => date("Y-m-d H:i:s"),
                        'observation' => 'insert manual',
                        'type' => 'old',
                        'status' => 'aprobado',
                    ]);
                }
            });
            
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }
}