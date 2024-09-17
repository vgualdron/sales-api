<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = $items = Payment::select('payments.*')
                            ->leftjoin('files', 'files.id', 'payments.file_id')
                            // ->leftjoin('files', 'lendings.id', 'interests.lending_id')
                            ->with('file')
                            // ->with('file')
                            // ->where('listing_id', '=', $idList)
                            // ->where('payments.date', '<=', date("Y-m-d h:i:s"))
                            // ->where('payments.amount', '=', NULL)
                            ->where('payments.status', '=', 'creado')
                            ->distinct()
                            ->orderBy('payments.date', 'asc')->get();
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
    
    public function getPaymentsForLending(Request $request, $idLending)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Payment::where('lending_id', '=', $idLending)->get();
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
    
    public function getPaymentsFromListCurrentDate(Request $request, $idList)
    {
        $date = date("Y-m-d");
        $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
        $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + 86399));
        
        try {
            $idUserSesion = $request->user()->id;
            $items = Payment::select('payments.*')
                                ->join('lendings', 'lendings.id', 'payments.lending_id')
                                ->where('lendings.listing_id', '=', $idList)
                                ->whereBetween('payments.updated_at', [$firstDate, $endDate])
                                ->distinct()->get();
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
            $items = Payment::where('id', '=', $id)->first();
            
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
            
            $item = Payment::create([
                'lending_id' => $request->lending_id,
                'date' => $request->date,
                'amount' => $request->amount,
                'color' => $request->color,
                'file_id' => $request->file_id
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
            'data' => $item,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        try {
            $items = Payment::find($id)
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
            'data' => $items,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $items = Payment::destroy($id);
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
}