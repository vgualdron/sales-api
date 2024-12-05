<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Novel;
use App\Models\Lending;
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
            $itemNovel = Novel::create([
                'document_number' => $request->document_number,
                'name' => $request->name,
                'phone' => $request->phone,
                'period' => $request->period,
                'quantity' => $request->quantity,
                'created_at' => date("Y-m-d H:i:s", (strtotime(date($request->created_at)) + 8640)),
                'lent_by' => $request->lent_by,
                'approved_by' => $request->approved_by,
                'address' => $request->address,
                'sector' => $request->sector,
                'district' => $request->district,
                'address_house' => $request->address,
                'address_house_sector' => $request->sector,
                'address_house_district' => $request->district,
                'observation' => $request->observation,
                'status' => $request->status,
                'attempts' => $request->attempts,
                'type_cv' => $request->type_cv,
                'site_visit' => $request->site_visit,
                'occupation' => $request->occupation,
                'user_send' => $request->user_send,
            ]);

            $countDays = 1;
            $amountFees = 1;
            $date = $request->date_lending;
            $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
           
            if ($request->period === 'diario') {
                $countDays = 21;
                $amountFees = 22;
            } else if ($request->period === 'semanal') {
                $countDays = 21;
                $amountFees = 3;
            } else if ($request->period === 'quincenal') {
                $countDays = 14;
                $amountFees = 1;
            }

            $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + (86400 * $countDays) + 86399));
            $paymentDate = date("Y-m-d H:i:s", (strtotime(date($date)) + (86400 * 2) + 86399));

            $itemLending = Lending::create([
                'nameDebtor' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'firstDate' => $firstDate,
                'endDate' => $endDate,
                'amount' => $request->amount_lending,
                'amountFees' => $amountFees,
                'percentage' => 32,
                'period' => $request->period,
                'order' => 1,
                'status' => 'open',
                'listing_id' => $request->listing_id,
                'new_id' => $itemNovel->id,
                'type' => 'N',
                'created_at' => $firstDate,
            ]);

            $itemPayment = Payment::create([
                'lending_id' => $itemLending->id,
                'date' => $paymentDate,
                'amount' => $request->amount_payment,
                'type' => 'nequi',
                'status' => 'aprobado',
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