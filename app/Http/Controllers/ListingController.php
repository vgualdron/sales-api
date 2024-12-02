<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Lending;
use App\Models\Payment;
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

            $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
            $currentDate = date("Y-m-d H:i:s");
      
            $itemList = Listing::find($idList);

            $itemPayment = Payment::selectRaw('
                    COUNT(*) as total_count, 
                    SUM(payments.amount) as total_amount, 
                    COUNT(DISTINCT lendings.id) as total_clients,
                    SUM(CASE WHEN payments.type = "nequi" THEN payments.amount ELSE 0 END) as total_amount_nequi,
                    SUM(CASE WHEN payments.type = "renovacion" THEN payments.amount ELSE 0 END) as total_amount_renovation,
                    SUM(CASE WHEN payments.type = "article" THEN payments.amount ELSE 0 END) as total_amount_article,
                    COUNT(CASE WHEN payments.type = "nequi" THEN 1 ELSE NULL END) as total_count_nequi,
                    COUNT(CASE WHEN payments.type = "renovacion" THEN 1 ELSE NULL END) as total_count_renovation,
                    COUNT(CASE WHEN payments.type = "article" THEN 1 ELSE NULL END) as total_count_article,
                    SUM(CASE WHEN payments.is_street = 0 THEN payments.amount ELSE 0 END) as total_secre,
                    SUM(CASE WHEN payments.is_street = 1 THEN payments.amount ELSE 0 END) as total_street')
                ->join('lendings', 'lendings.id', '=', 'payments.lending_id')
                ->whereBetween('payments.date', [$date." 00:00:00", $date." 23:59:59"])
                ->where('lendings.listing_id', $idList)
                // ->where('payments.type', 'nequi')
                ->whereIn('payments.status', ['aprobado', 'verificado'])
                ->first();

            $itemRenove = Lending::selectRaw('COUNT(*) as total_count, SUM(amount) as total_amount')
                        ->whereBetween('created_at', ["{$date} 00:00:00", "{$date} 23:59:59"])
                        ->where('listing_id', $idList)
                        ->where('status', 'open')
                        ->where('type', 'R')
                        ->first();

            $itemNovel = Lending::selectRaw('COUNT(*) as total_count, SUM(amount) as total_amount')
                        ->whereBetween('created_at', ["{$date} 00:00:00", "{$date} 23:59:59"])
                        ->where('listing_id', $idList)
                        ->where('status', 'open')
                        ->where('type', 'N')
                        ->first();
                        
            $data = [
                'itemList' => $itemList,
                'itemPayment' => $itemPayment,
                'itemRenove' => $itemRenove,
                'itemNovel' => $itemNovel,
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