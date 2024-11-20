<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Listing::with('userCollector')
                                ->with('userLeader')
                                ->with('userAuthorized')
                                ->with('lendings')
                                ->where('status', '=', 'activa')
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
    
    public function getMine(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Listing::where('status', '=', 'activa')
                            ->where(function ($query) use ($idUserSesion) {
                                $query->where('user_id_collector', '=', $idUserSesion)
                                    ->orWhere('user_id_leader', '=', $idUserSesion)
                                    ->orWhere('user_id_authorized', '=', $idUserSesion);
                            })
                            ->with('userCollector')
                            ->with('userLeader')
                            ->with('userAuthorized')
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

    public function show(Request $request, $id)
    {
        try {
            $items = Listing::where('id', '=', $id)->with('userCollector')->with('userLeader')->with('userAuthorized')->first();
            
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
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            
            $item = Listing::create([
                'name' => $request->name,
                'status' => $request->status,
                'user_id_collector' => $request->user_id_collector,
                'user_id_leader' => $request->user_id_leader,
                'user_id_authorized' => $request->user_id_authorized,
                'user_id' => $idUserSesion
            ]);
            
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
            'message' => [
                [
                    'text' => 'Creado con éxito.',
                    'detail' => null,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        try {
            $items = Listing::find($id)
                        ->update($request->all());
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
            'message' => [
                [
                    'text' => 'Modificado con éxito.',
                    'detail' => null,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $items = Listing::destroy($id);
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
            'message' => [
                [
                    'text' => 'Eliminado con éxito.',
                    'detail' => null,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }
}