<?php

namespace App\Http\Controllers;

use App\Models\Nequi;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class NequiController extends Controller
{
    public function index(Request $request, $idList)
    {
        try {
            $idUserSesion = $request->user()->id;
            // $items = Nequi::where('status', 'activo')->where('listing_id', $idList)->orderBy('order', 'asc')->get();
            $items = Nequi::where('status', 'activo')
                    ->where('listing_id', $idList)
                    ->orWhere('global', 1)
                    ->orderBy('order', 'asc')
                    ->get();
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
}