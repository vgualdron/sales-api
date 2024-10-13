<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PaymentController extends Controller
{
    public function index(Request $request, $status)
    {
        try {
            $explodeStatus = explode(',', $status);
            $idUserSesion = $request->user()->id;
            $items = $items = Payment::select(
                                'payments.*',
                                'lendings.nameDebtor',
                                'listings.name as listName',
                                'users.name as userName',
                                'files.url as urlFile',
                                'files.type as typeFile',
                            )->leftjoin('files', 'files.id', 'payments.file_id')
                            ->join('lendings', 'lendings.id', 'payments.lending_id')
                            ->join('listings', 'listings.id', 'lendings.listing_id')
                            ->join('users', 'users.id', 'listings.user_id_collector')
                            ->with('file')
                            ->when($status !== 'all', function ($q) use ($explodeStatus) {
                                return $q->whereIn('payments.status', $explodeStatus);
                            })
                            ->where('payments.type', '=', 'nequi')
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
                'observation' => $request->observation,
                'file_id' => $request->file_id,
                'type' => $request->type,
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
            'message' => [
                [
                    'text' => 'Pago modificado con éxito',
                    'detail' => 'Se modificó el pago con éxito',
                ]
                ],
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