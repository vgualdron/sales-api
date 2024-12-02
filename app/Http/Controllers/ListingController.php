<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Lending;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
                'user_id' => $idUserSesion,
                'city_id' => $request->city_id,
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

    public function getDelivery(Request $request, $idList, $date)
    {
        $data = null;
        try {
            $idUserSesion = $request->user()->id;

            $itemLending = Lending::find(10);

            $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
            $currentDate = date("Y-m-d H:i:s");
      
            $itemList = Listing::find($idList);

            $itemTransfer = DB::select("SELECT
                COUNT(*) as amount,
                SUM(pay.amount) as total
                FROM payments pay
                JOIN lendings len ON (len.id = pay.lending_id)
                JOIN listings lis ON (lis.id = len.listing_id)
                WHERE date BETWEEN '2024-11-21 00:00:00' AND '2024-11-21 23:59:59'
                AND lis.id = 1
                AND pay.type = 'nequi';");

            $data = [
                'itemLending' => $itemLending,
                'itemList' => $itemList,
                'itemTransfer' => $itemTransfer ? $itemTransfer : null,
                'date' => $date,
            ];

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
            'data' => $data,
        ], JsonResponse::HTTP_OK);
    }
}